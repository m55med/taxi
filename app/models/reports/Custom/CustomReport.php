<?php

namespace App\Models\Reports\Custom;

use App\Core\Model;
use PDO;
use PDOException;

class CustomReport extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all table names from the database.
     * @return array
     */
    public function getTables(): array
    {
        try {
            $stmt = $this->db->prepare("SHOW TABLES");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            // In a real application, you'd log this error.
            return [];
        }
    }

    /**
     * Get all column names for a given table.
     * @param string $tableName
     * @return array
     */
    public function getColumnsForTable(string $tableName): array
    {
        $allTables = $this->getTables();
        if (!in_array($tableName, $allTables)) {
            return [];
        }

        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM `{$tableName}`");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Sanitizes and validates the columns requested by the user.
     *
     * @param array $requestedColumns Columns in 'table.column' format.
     * @param array $availableTables  List of tables selected for the report.
     * @return array Sanitized columns in '`table`.`column`' format.
     */
    private function getSanitizedColumns(array $requestedColumns, array $availableTables): array
    {
        $sanitized = [];
        $allAvailableColumns = [];
        foreach($availableTables as $table) {
            $cols = $this->getColumnsForTable($table);
             if ($table === 'users') {
                $sensitiveColumns = ['password'];
                $cols = array_diff($cols, $sensitiveColumns);
            }
            foreach($cols as $col) {
                $allAvailableColumns[] = "{$table}.{$col}";
            }
        }

        foreach ($requestedColumns as $reqCol) {
            if (in_array($reqCol, $allAvailableColumns)) {
                list($table, $col) = explode('.', $reqCol);
                $sanitized[] = "`{$table}`.`{$col}`";
            }
        }
        return $sanitized;
    }

    public function buildAndRunQuery(array $tables, array $columns, array $filters, array $joins): array
    {
        if (empty($tables) || empty($columns)) {
            return [[], "الرجاء اختيار جدول وعمود واحد على الأقل."];
        }

        // Validate tables
        $allTables = $this->getTables();
        foreach ($tables as $table) {
            if (!in_array($table, $allTables)) {
                return [[], "جدول غير صالح: {$table}"];
            }
        }
        
        $sanitizedColumns = $this->getSanitizedColumns($columns, $tables);
        if(empty($sanitizedColumns)) {
             return [[], "الأعمدة المحددة غير صالحة."];
        }

        $queryColumns = implode(', ', $sanitizedColumns);
        $baseTable = "`{$tables[0]}`";
        $query = "SELECT {$queryColumns} FROM {$baseTable}";

        // Build Joins
        $allowedJoinTypes = ['INNER', 'LEFT', 'RIGHT'];
        if (count($tables) > 1 && !empty($joins)) {
            foreach ($joins as $join) {
                if (
                    in_array($join['left_table'], $tables) &&
                    in_array($join['right_table'], $tables) &&
                    in_array($join['type'], $allowedJoinTypes) &&
                    !empty($join['left_column']) && !empty($join['right_column']) &&
                    in_array($join['left_column'], $this->getColumnsForTable($join['left_table'])) &&
                    in_array($join['right_column'], $this->getColumnsForTable($join['right_table']))
                ) {
                   $query .= " {$join['type']} JOIN `{$join['right_table']}` ON `{$join['left_table']}`.`{$join['left_column']}` = `{$join['right_table']}`.`{$join['right_column']}`";
                }
            }
        }


        // Build Filters
        $whereClauses = [];
        $bindings = [];
        $allowed_operators = ['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE'];

        if (!empty($filters)) {
            foreach ($filters as $filter) {
                if (
                    !empty($filter['column']) &&
                    isset($filter['value']) && $filter['value'] !== '' &&
                    !empty($filter['operator']) && in_array($filter['operator'], $allowed_operators)
                ) {
                    list($table, $column) = explode('.', $filter['column']);
                    if(in_array($table, $tables) && in_array($column, $this->getColumnsForTable($table))) {
                        $operator = $filter['operator'];
                        $value = $filter['value'];
                        
                        if (str_contains(strtoupper($operator), 'LIKE')) {
                            $value = '%' . $value . '%';
                        }
                        
                        $whereClauses[] = "`{$table}`.`{$column}` {$operator} ?";
                        $bindings[] = $value;
                    }
                }
            }
        }

        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $displayQuery = $query;
        $tempBindings = $bindings;
        while (strpos($displayQuery, '?') !== false && !empty($tempBindings)) {
            $pos = strpos($displayQuery, '?');
            $binding = array_shift($tempBindings);
            $quotedBinding = is_string($binding) ? "'" . addslashes($binding) . "'" : $binding;
            $displayQuery = substr_replace($displayQuery, $quotedBinding, $pos, 1);
        }

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($bindings);
            $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($reportData)) {
                return [[], "لم يتم العثور على نتائج. الاستعلام: " . $displayQuery];
            }
            
            return [$reportData, $displayQuery];
        } catch (PDOException $e) {
            return [[], "خطأ في تنفيذ الاستعلام: " . $e->getMessage()];
        }
    }
}
