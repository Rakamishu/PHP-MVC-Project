<?php

use App\Core\Controller as Controller;
use App\Models\Pagination as Pagination;

class News extends Controller
{
    
    public function index()
    {
        $news = $this->model('News\News');
        
        /**
         * Get the current page. Set it to 1 by default if page is not set.
         */
        $currentPage = isset($_GET['page']) ? $_GET['page'] : 1;
        if($currentPage < 1)
        {
            $currentPage = 1;
        }
        
        /**
         * Send the news from the current page and pagination as an array to the view.
         */
        $data['news'] = $news->allNews($currentPage, 10);
        $pagination = new Pagination('news', $currentPage, 10);
        $data['pagination'] = $pagination->createLinks(SITE_ADDR.'/public/news?page=');
        
        $this->view('header', ['title' => 'Most Recent News']);
        $this->view('menu');
        $this->view('news/index', $data); 
        $this->view('footer');
    }
    
    public function read($newsid = null) //$newsid is automatically added as a parameter
    {
        if($newsid == null){
            error_404();
        }
        
        $news = $this->model('News\News');
        $data = $news->viewNews($newsid);
        
        if($data)
        {
            $this->view('header', ['title' => $data->title]);
            $this->view('menu');
            $this->view('news/view', $data);
            $this->view('footer');
        }
        else 
        {
            error_404();
        }
    }
    
}