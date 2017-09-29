<?php

namespace App\Models\Auth;

class UserData
{    
    
    protected $db;
    
    public function __construct() 
    {
        $this->db = \App\Core\Database::getInstance();
    }
    
    public function userData(int $userid)
    {
        $query = $this->db->getRows("SELECT * FROM users WHERE userid = ?", [$userid]);
        
        return $query;
    }
    
    
}