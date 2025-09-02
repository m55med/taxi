<?php

namespace App\Core;

use PDO;

class Model
{
    protected $db;
    protected $stmt;

    public function __construct()
    {
        // The getInstance method no longer needs a config parameter.
        $this->db = Database::getInstance();
    }

    // Prepares statement with query
    public function query($sql)
    {
        $this->stmt = $this->db->prepare($sql);
    }

    // Binds the prep statement
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
    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single record as object
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get row count
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    // Get the last inserted ID
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
    
    // Transactions
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }
    
    public function commit()
    {
        return $this->db->commit();
    }
    
    public function rollBack()
    {
        return $this->db->rollBack();
    }
    
    // Get database connection
    public function getDb()
    {
        return $this->db;
    }
}
