<?php

namespace App\Models\Admin;

use App\Core\Database as Database;

class Dashboard
{
    protected $db;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
    }
    
    public function totalUsers()
    {
        $totalUsers = $this->db->getRows("SELECT COUNT(*) as totalUsers FROM users");
        return $totalUsers[0]->totalUsers;
    }
    
    public function totalNews()
    {
        $totalNews = $this->db->getRows("SELECT COUNT(*) as totalNews FROM news");
        return $totalNews[0]->totalNews;
    }
}