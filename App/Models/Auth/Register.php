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
        /* Hash the password */
        $passwordEncryption = new \App\Models\Auth\PasswordEncryption();
        $this->password_hash = $passwordEncryption->encrypt($this->password);
        
        /* Validate the user input */
        if($this->validate())
        {
            FlashMessage::error(implode('<br />', $this->validate()));
            redirect(SITE_ADDR.'/public/user/signup');
        }
        
        /* Add the new user to the Database */
        $this->registerAddToDb();
        /* Send email */
        $this->sendMail();
        
        /* Login the newly registered user. */
        $login = new \App\Models\Auth\Login([
            'username' => $this->username, 
            'password' => $this->password, 
            'remember_me' => false, 
            'csrf' => \App\Core\CSRF::generate(), 
            'user_agent' => md5($_SERVER['HTTP_USER_AGENT']), 
            'redirect' => "http://localhost/MVC/public/"
            ]);
        $login->login();
    }
    
    private function registerAddToDb()
    {
        $this->db->insertRow("INSERT INTO users 
            (username, email, password) 
            VALUES 
            (?, ?, ?)", [
                $this->username, 
                $this->email, 
                $this->password_hash
            ]);
    }
    
    private function validate()
    {
        if(\App\Core\CSRF::check($this->csrf) === false)
        {
            $err[] = 'CSRF error.';
        }
        
        /* Validate email */
        if(!$this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL))
        {
            $err[] = 'Invalid E-mail address.';
        }
        
        /* Check if the email is not taken by another user */
        $email_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE email = ?", [$this->email]);
        if($email_unique->count > 0)
        {
            $err[] = 'This email is already being used by another user';
        }
        
        /* Check if the password is too short. */
        if(strlen($this->password) <= 5)
        {
            $err[] = 'The password is too short.';
        }
        
        /* Check if the email is not taken by another user */
        $username_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE username = ?", [$this->username]);
        if($username_unique->count > 0)
        {
            $err[] = 'This username is already being used by another user.';
        }
        
        /* Validate username */
        if(preg_match("/[A-Za-z0-9-_]/", $this->username) == false)
        {
            $err[] = "Invalid characters for username. Allowed characters: letters, numbers, dash and underscore.";
        }
        
        /* Validate recaptcha */
        $recaptcha = new \ReCaptcha\ReCaptcha(GOOGLE_CAPTCHA);
        $resp = $recaptcha->verify($this->recaptcha, $_SERVER['REMOTE_ADDR']);
        if (!$resp->isSuccess()) {
            $err[] = 'Error Authenticating'; 
        }
        
        /* Return the array with errors or return false if none have been registered. */
        return empty($err) ? false : $err;
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
        return true;
    }
      
}