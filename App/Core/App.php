<?php

namespace App\Core;

class App
{
    
    protected $controller = 'home';
    protected $method = 'index';
    protected $params = [];
    
    public function __construct()
    {
        $url = $this->parseUrl();
        
        if(file_exists('../App/Controllers/'.$url[0].'.php'))
        {
            $this->controller = $url[0];
            unset($url[0]);
        }
        
        require_once '../App/Controllers/'.$this->controller.'.php';
        $this->controller = new $this->controller;
        
        if(isset($url[1]))
        {
            if(method_exists($this->controller, $url[1]))
            {
                $this->method = $url[1];
                unset($url[1]);
            }
        }
        
        $this->params = $url ? array_values($url) : [];
        
        /* Check if the called method is public. 
         * This is to prevent the user from opening private methods from the controller, e.g: /public/user/change_email which is used to access the model for changing the emails.
         */
        $reflection = new \ReflectionMethod($this->controller, $this->method);
        if(!$reflection->isPublic())
        {
            error_404();
        }
        
        call_user_func_array([$this->controller, $this->method], $this->params);
        
        if(isset($_COOKIE['cookie_hash']))
        {
            $login = new \App\Models\Auth\Login();
            $username_session = isset($_SESSION['username']) ? $_SESSION['username'] : null;
            $login->loginWithCookie($_COOKIE['cookie_hash'], $username_session, $_SERVER['HTTP_USER_AGENT']);
        }
       
        /* Clear all Flash Messages */
        FlashMessage::clear();
    }
    
    public function parseUrl()
    {
        if(isset($_GET['url']))
        {
            return $url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
    }
        
}