<?php

namespace App\Models\Auth;

use \App\Core\FlashMessage;
use \App\Core\Mail as Mail;

class FacebookRegister extends Register
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

    public function register()
    {
        $this->validate();
        
        /* Add the new user to the Database */
        $this->db->insertRow("INSERT INTO users (username, email, facebook_id) VALUES (?, ?, ?)", [
            $this->username, 
            $this->email, 
            $this->facebook_id
            ]);
        $this->sendMail();
        
        /* Login the newly registered user. */
        $this->login();
    }
    
    private function validate()
    {
        if(\App\Core\CSRF::check($this->csrf) === false) {
            $err[] = 'CSRF error.';
        }
        
        /* Email */
        if(!$this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL)) {
            $err[] = 'Invalid E-mail address.';
        }
        
        if(!$this->checkIfEmailIsTaken($this->email)) {
            $err[] = 'This email is already being used by another user';
        }
        
        /* Username */
        if(!$this->checkIfUsernamelIsTaken($this->username)) {
            $err[] = 'This username is already being used by another user.';
        }
        
        if(preg_match("/[A-Za-z0-9-_]/", $this->username) == false) {
            $err[] = "Invalid characters for username. Allowed characters: letters, numbers, dash and underscore.";
        }
        
        if(strlen($this->username) < 4) {
            $err[] = "Username must have more than 4 characters.";
        }
        
        if(!empty($err)) {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/signup/facebook');
        }
    }
    
    private function login()
    {
        $login = new \App\Models\Auth\FacebookLogin([
            'username' => $this->username, 
            'facebook_id' => $this->facebook_id, 
            'remember_me' => true, 
            'csrf' => \App\Core\CSRF::generate(), 
            'user_agent' => md5($_SERVER['HTTP_USER_AGENT']), 
            'redirect' => SITE_ADDR.'/public'
            ]);
        $login->login();
    }
      
}