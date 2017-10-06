<?php

namespace App\Models\News;

use App\Models\Pagination as Pagination;
use App\Core\FlashMessage as FlashMessage;

class News
{
    
    protected $db;
    private $title;
    private $content;
    private $csrf;
    
    public function __construct() 
    {
        $this->db = \App\Core\Database::getInstance();
    }
    
    /**
     * Return most recent news
     * @param int $currentPage
     * @param int $perPage  
     * @return type
     */
    public function allNews(int $currentPage, int $perPage)
    {
        $startFrom = ($currentPage*$perPage) - $perPage;
        
        $getRow = $this->db->getRows("SELECT * FROM news ORDER BY id DESC LIMIT $startFrom, $perPage");
        
        return $getRow;
    }
    
    /**
     * Returns data about particular news
     * @param int $newsid - ID of the news
     * @return array
     */
    public function viewNews(int $newsid)
    {        
        $getRow = $this->db->getRow("SELECT * FROM news WHERE id = ?", [$newsid]);
        
        return $getRow;
    }
    
    /**
     * Add new news into the database
     * @param string $title Title of the news
     * @param string $content Content of the news
     */
    public function add(string $title, string $content, string $csrf)
    {
        $this->title = $title;
        $this->content = $content;
        $this->csrf = $csrf;
        
        if($this->validateAdd())
        {
            FlashMessage::error(implode('<br />', $this->validateAdd()));
            redirect(SITE_ADDR.'/public/admin/news/add');
        }
            
        $query = $this->db->insertRow("INSERT INTO news (title, content) VALUES (?, ?)", [$this->title, $this->content]);
        if($query)
        {
            FlashMessage::success("Successful!");
            redirect(SITE_ADDR.'/public/admin/news/add');
        }
    }
    
    private function validateAdd()
    {
       if($this->csrf != \App\Core\CSRF::check($this->csrf))
        {
            $err[] = 'CSRF error.';
        }
        
        if(empty($this->title) || empty($this->content) || empty($this->csrf))
        {
            $err[] = 'All fields are required';
        }
        
        return empty($err) ? false : $err;
    }
    
    /**
     * Edits the content of a particular news
     * @param string $title New title of the news
     * @param string $content New content of the news
     * @param int $id The ID of the news that is being edited
     */
    public function edit(string $title, string $content, int $id, string $csrf)
    {
        if(empty($title) || empty($content))
        {
            Flashmessage::error("All fields are required");
            redirect(SITE_ADDR.'/public/admin/news/edit/'.$id);
        }
            
        $query = $this->db->updateRow("UPDATE news SET title = ?, content = ? WHERE id = ?", [$title, $content, $id]);
        if($query)
        {
            FlashMessage::success("Successful!");
            redirect(SITE_ADDR.'/public/admin/news/edit/'.$id);
        }
        var_dump($query);
    }
    
}
