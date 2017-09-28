<?php

use \App\Core\Controller as Controller;
use \App\Models\Auth\Authenticate as Authenticate;
use \App\Core\FlashMessage as FlashMessage;

class Admin extends Controller
{    
    
    private $user_type;
    
    public function __construct() {
        if(isset($_SESSION['type']))
        {
            $this->user_type = $_SESSION['type'];
        }
        
        /**
         * Authenticate if the user is logged in and has Admin Privileges
         */
        $auth = new Authenticate($this->user_type);
        if(!$auth->isAdmin())
        {
            redirect(SITE_ADDR.'/public/home');
        }
    }
    
    public function index()
    {
        $news = $this->model('Admin\Dashboard'); 
        $data['totalUsers'] = $news->totalUsers();
        $data['totalNews'] = $news->totalNews();
        
        $this->view('header', ['title' => 'Admin Panel Dashboard']);
        $this->view('menu');
        $this->view('admin/dashboard', $data); 
        $this->view('footer');
    }
    
    public function news($action, $id = null)
    {        
        /**
         * Separating the different actions.
         */
        switch($action)
        {
            default:
                error_404();
                break;
                
            case "add":
                $this->add_news();
                break;
            
            case "edit":
                $this->edit_news($id);
                break;
        }
    }
    
    private function add_news()
    {
        if(isset($_POST['add']))
        {
            $news = $this->model('News\News'); 
            $data = $news->add($_POST['title'], $_POST['content'], $_POST['csrf']);
        }
        else 
        {
            $this->view('header', ['title' => 'Admin Panel - Add News']);
            $this->view('menu');
            $this->view('news/add', ['csrf' => \App\Core\CSRF::generate()]); 
            $this->view('footer');  
        }
    }
    
    private function edit_news($id)
    {
        if(empty($id))
        {
            error_404();
        }

        if(isset($_POST['edit']))
        {
            $news = $this->model('News\News'); 
            $data = $news->edit($_POST['title'], $_POST['content'], $id, $_POST['csrf']);
        }
        else 
        {
            $news = $this->model('News\News'); 
            $data['news'] = $news->viewNews($id);
            $data['csrf'] = \App\Core\CSRF::generate();
            
            /**
             * If the news doesn't exist, redirect to 404.
             */
            if(!$data['news'])
            {
                FlashMessage::error("News with this ID does not exist.");
                error_404();
            }

            $this->view('header', ['title' => 'Admin Panel - Edit News']);
            $this->view('menu');
            $this->view('news/edit', $data); 
            $this->view('footer');  
        }
    }
    
}
