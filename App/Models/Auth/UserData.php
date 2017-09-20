<?php

namespace App\Models\Auth;

use App\Core\Database;

class UserData
{    
    
    protected $db;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
    }
    
    public function userData($userid)
    {
        $query = $this->db->getRows("SELECT * FROM users WHERE userid = ?", [$userid]);
        
        return $query;
    }
    
    
}