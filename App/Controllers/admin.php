<?php

use \App\Core\FlashMessage as FlashMessage;

class Admin extends \App\Core\Controller
{    
    
    private $user_type;
    
    public function __construct() {
        if(isset($_SESSION['type']))
        {
            $this->user_type = $_SESSION['type'];
        }
        
        /*Authenticate if the user is logged in and has Admin Privileges  */
        $auth = new \App\Models\Auth\Authenticate($this->user_type);
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
    
    public function news(string $action, int $id = null)
    {
        /* Separating the different actions. */
        switch($action)
        {
            default:
                error_404();
                break;
                
            case "add":
                $this->addNews();
                break;
            
            case "edit":
                $this->editNews($id);
                break;
        }
    }
    
    private function addNews()
    {
        if(isset($_POST['add']))
        {
            $news = $this->model('News\News', ['title' => $_POST['title'], 'content' => $_POST['content'], 'csrf' => $_POST['csrf']]); 
            $news->add();
        }
        else 
        {
            $this->view('header', ['title' => 'Admin Panel - Add News']);
            $this->view('menu');
            $this->view('news/add', ['csrf' => \App\Core\CSRF::generate()]); 
            $this->view('footer');  
        }
    }
    
    private function editNews(int $id)
    {
        if(empty($id))
        {
            error_404();
        }

        if(isset($_POST['edit']))
        {
            $news = $this->model('News\News', ['title' => $_POST['title'], 'content' => $_POST['content'], 'id' => $id, 'csrf' => $_POST['csrf']]); 
            $news->edit();
        }
        else 
        {
            $news = $this->model('News\News', ['id' => $id]); 
            $data['news'] = $news->viewNews();
            $data['csrf'] = \App\Core\CSRF::generate();
            
            /* If the news doesn't exist, redirect to 404. */
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
