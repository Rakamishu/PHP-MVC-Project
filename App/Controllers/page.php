<?php

use App\Core\Controller;

class Page extends Controller
{
    
    /**
     * Open file from view/pages/ folder.
     * @param String
     */
    public function index($page)
    {
        if(!file_exists(ROOT.'/view/pages/'.$page.'.php'))
        {
            error_404();
        }
        
        switch($page)
        {
            case "404":
                $title = 'Page not found';
                break;
            case "about":
                $title = 'About us';
                break;
        }
        
        $this->view('header', ['title' => $title]);
        $this->view('menu');
        $this->view('pages/'.$page);
        $this->view('footer');
    }
    
}