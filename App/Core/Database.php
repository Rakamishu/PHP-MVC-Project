<?php

namespace App\Core;

use \PDO;

class Database
{
    
    private static $instance = NULL;
    private $conn = NULL;
        
    private function __construct()
    {
        try  {
            $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ];
            
            $this->conn = new PDO("mysql:dbname=".DB_NAME.";host=".DB_HOST.";charset=utf8", DB_USER, DB_PASSWORD, $opt);
        } catch (PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public static function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getRow($query, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function getRows($query, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function insertRow($query, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return TRUE;
        } catch (PDOException $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function updateRow($query, $params = [])
    {
        return $this->insertRow($query, $params);
    }
    
    public function deleteRow($query, $params = [])
    {
        return $this->insertRow($query, $params);
    }
    
}

