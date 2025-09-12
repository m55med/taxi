<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;
    private $stmt;

    private function __construct()
    {
        $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
        $dbName = $_ENV['DB_DATABASE'] ?? '';
        $dbUser = $_ENV['DB_USERNAME'] ?? 'root';
        $dbPass = $_ENV['DB_PASSWORD'] ?? '';

        try {
            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        } catch (PDOException $e) {
            // In production, log the error instead of dying.
            error_log($e->getMessage());
            die('Database Connection Failed. Please check logs for details.');
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Prepare statement with query
    public function query($sql)
    {
        $this->stmt = $this->pdo->prepare($sql);
        return $this->stmt;
    }

    // Bind values
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Execute the prepared statement
    public function execute()
    {
        return $this->stmt->execute();
    }

    // Get result set as array of objects
    public function resultSet($fetchMode = PDO::FETCH_ASSOC)
    {
        $this->execute();
        return $this->stmt->fetchAll($fetchMode);
    }

    // Get single record as object
    public function single($fetchMode = PDO::FETCH_ASSOC)
    {
        $this->execute();
        return $this->stmt->fetch($fetchMode);
    }

    // Get row count
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }
    
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    
    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }
    
    // Transaction methods
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }
}