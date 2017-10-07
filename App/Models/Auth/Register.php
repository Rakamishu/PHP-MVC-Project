<?php

namespace App\Models\Auth;

use \App\Core\FlashMessage;
use \App\Core\Mail as Mail;

class Register
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
        
        /* Hash the password before registering */
        $passwordEncryption = new \App\Models\Auth\PasswordEncryption();
        $this->password_hash = $passwordEncryption->encrypt($this->password);
        
        /* Add the new user to the Database */
        $this->db->insertRow("INSERT INTO users (username, email, password) VALUES (?, ?, ?)", [
            $this->username, 
            $this->email, 
            $this->password_hash
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
        
        if(!$this->checkIfEmailIsTaken()) {
            $err[] = 'This email is already being used by another user';
        }
        
        /* Password */
        if(strlen($this->password) <= 5) {
            $err[] = 'The password is too short.';
        }
        
        /* Username */
        if(!$this->checkIfUsernamelIsTaken()) {
            $err[] = 'This username is already being used by another user.';
        }
        
        if(preg_match("/[A-Za-z0-9-_]/", $this->username) == false) {
            $err[] = "Invalid characters for username. Allowed characters: letters, numbers, dash and underscore.";
        }
        
        if(strlen($this->username) < 4) {
            $err[] = "Username must have more than 4 characters.";
        }
        
        if(!$this->validateRecaptcha()) {
            $err[] = 'Error Authenticating'; 
        }
        
        if(!empty($err)) {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/signup');
        }
    }
    
    private function validateRecaptcha()
    {
        $recaptcha = new \ReCaptcha\ReCaptcha(GOOGLE_CAPTCHA);
        $resp = $recaptcha->verify($this->recaptcha, $this->ip);
        if ($resp->isSuccess()) {
            return true;
        }
        return false;
    }
        
    private function checkIfUsernamelIsTaken()
    {
        $username_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE username = ?", [$this->username]);
        if($username_unique->count == 0)
        {
            return true;
        }
        return false;
    }
    
    private function checkIfEmailIsTaken()
    {
        $email_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE email = ?", [$this->email]);
        if($email_unique->count == 0) {
            return true;
        }
        return false;
    }
    
    private function sendMail()
    {
        $mail = new Mail();
        $mail->send(
            $this->email, 
            "Welcome to ".$GLOBALS['site_name'], 
            "Hello, $this->username <br /><br />Thank you for joining us here at ".$GLOBALS['site_name']."! "
            . "You can now use your account to create your personal calendar with your favorite TV Series! <br /><br /> "
            . "The ".$GLOBALS['site_name']." team"
        );
    }
    
    private function login()
    {
        $login = new \App\Models\Auth\Login([
            'username' => $this->username, 
            'password' => $this->password, 
            'remember_me' => false, 
            'csrf' => \App\Core\CSRF::generate(), 
            'user_agent' => md5($_SERVER['HTTP_USER_AGENT']), 
            'redirect' => SITE_ADDR
            ]);
        $login->login();
    }
      
}