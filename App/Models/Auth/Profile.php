<?php

namespace App\Models\Auth;

class Profile
{    
    
    private $db;
    
    public function __construct($data = null) 
    {
        $this->db = \App\Core\Database::getInstance();
        if(isset($data))
        {
            foreach($data as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }
    
    public function profile()
    {
        $query = $this->db->getRows("SELECT * FROM users WHERE userid = ?", [$this->id]);
        return $query;
    }
    
}