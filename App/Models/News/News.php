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
    
    /* Returns data about particular news */
    public function viewNews()
    {        
        $getRow = $this->db->getRow("SELECT * FROM news WHERE id = ?", [$this->id]);
        return $getRow;
    }
    
    /* Add new news into the database */
    public function add()
    {
        $this->validate();
            
        $query = $this->db->insertRow("INSERT INTO news (title, content) VALUES (?, ?)", [$this->title, $this->content]);
        FlashMessage::success("Successful!");
        redirect(SITE_ADDR.'/public/admin/news/add');
    }
    
    /* Edits the content of a particular news */
    public function edit()
    {        
        $this->validate();
            
        $query = $this->db->updateRow("UPDATE news SET title = ?, content = ? WHERE id = ?", [$this->title, $this->content, $this->id]);
        FlashMessage::success("Successful!");
        redirect(SITE_ADDR.'/public/admin/news/edit/'.$this->id);
    }
    
    private function validate()
    {
        if($this->csrf != \App\Core\CSRF::check($this->csrf)){
            $err[] = 'CSRF error.';
        }
        
        if(empty($this->title) || empty($this->content) || empty($this->csrf)) {
            $err[] = 'All fields are required';
        }
        
        if($err)
        {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/admin/news/add');
        }
    }
}
