<?php

namespace App\Models\Admin;

class Dashboard
{
    protected $db;
    
    public function __construct() 
    {
        $this->db = \App\Core\Database::getInstance();
    }
    
    public function totalUsers() : int
    {
        $totalUsers = $this->db->getRows("SELECT COUNT(*) as totalUsers FROM users");
        return $totalUsers[0]->totalUsers;
    }
    
    public function totalNews() : int
    {
        $totalNews = $this->db->getRows("SELECT COUNT(*) as totalNews FROM news");
        return $totalNews[0]->totalNews;
    }
}