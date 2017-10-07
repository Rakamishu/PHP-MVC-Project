<?php

namespace App\Models\News;

use App\Models\Pagination as Pagination;
use App\Core\FlashMessage as FlashMessage;

class News
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
    public function viewNews()
    {        
        $getRow = $this->db->getRow("SELECT * FROM news WHERE id = ?", [$this->id]);
        return $getRow;
    }
    
    /**
     * Add new news into the database
     * @param string $title Title of the news
     * @param string $content Content of the news
     */
    public function add()
    {
        if($this->validate())
        {
            FlashMessage::error(implode('<br />', $this->validate()));
            redirect(SITE_ADDR.'/public/admin/news/add');
        }
            
        $query = $this->db->insertRow("INSERT INTO news (title, content) VALUES (?, ?)", [$this->title, $this->content]);
        if($query)
        {
            FlashMessage::success("Successful!");
            redirect(SITE_ADDR.'/public/admin/news/add');
        } 
    }
    
    /**
     * Edits the content of a particular news
     * @param string $title New title of the news
     * @param string $content New content of the news
     * @param int $id The ID of the news that is being edited
     */
    public function edit()
    {        
        if($this->validate())
        {
            FlashMessage::error(implode('<br />', $this->validate()));
            redirect(SITE_ADDR.'/public/admin/news/edit/'.$this->id);
        }
            
        $query = $this->db->updateRow("UPDATE news SET title = ?, content = ? WHERE id = ?", [$this->title, $this->content, $this->id]);
        if($query)
        {
            FlashMessage::success("Successful!");
            redirect(SITE_ADDR.'/public/admin/news/edit/'.$this->id);
        }
    }
    
    private function validate()
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
}
