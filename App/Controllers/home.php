<?php

use App\Core\Controller as Controller;

class Home extends Controller
{    
    
    public function index()
    {
        //$news = $this->model('News\NewsRepository'); //instantiate model NewsRepository
        //$data = $news->index();
        
        $this->view('header', ['title' => 'Most Recent News']);
        $this->view('menu');
        $this->view('home/index');  // Locates the file index.php in folder news
        $this->view('footer');
    }
    
}