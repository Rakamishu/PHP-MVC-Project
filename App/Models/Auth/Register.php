<?php

namespace App\Models\Auth;

use \App\Core\FlashMessage;
use \App\Core\Mail as Mail;
use \DateTime;

class Register
{    
    
    protected $db;
    
    public function __construct() 
    {
        $this->db = \App\Core\Database::getInstance();
    }

    public function register(string $username, string $email, string $password, string $csrf)
    {
        if($csrf != \App\Core\CSRF::check($csrf))
        {
            $err[] = 'CSRF error.';
        }
        
        /**
         * Check for empty fields
         */
        if(empty($username) || empty($password))
        {
            $err[] = 'All fields are required.';                
        }
        
        /**
         * Validate email
         */
        if(!$email = filter_var($email, FILTER_SANITIZE_EMAIL))
        {
            $err[] = 'Invalid E-mail address.';
        }
        
        /**
         * Check if the email is not taken by another user
         */
        $email_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE email = ?", [$email]);
        if($email_unique->count > 0)
        {
            $err[] = 'This email is already being used by another user';
        }
        
        /**
         * Check if the password is too short.
         */
        if(strlen($password) <= 5)
        {
            $err[] = 'The password is too short.';
        }
        
        /**
         * Check if the email is not taken by another user
         */
        $username_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE username = ?", [$username]);
        if($username_unique->count > 0)
        {
            $err[] = 'This username is already being used by another user.';
        }
        
        /**
         * Validate username
         */
        if(preg_match("/[A-Za-z0-9-_]/", $username) == false)
        {
            $err[] = "Invalid characters for username. Allowed characters: letters, numbers, dash and underscore.";
        }
        
        /**
         * Check if the captcha is passed
         */
        $recaptcha = new \ReCaptcha\ReCaptcha(GOOGLE_CAPTCHA);
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if (!$resp->isSuccess()) {
            $err[] = 'Error Authenticating'; 
        }
        
        /**
         * Check if any errors have been registered so far, otherwise proceed to registering the user
         */
        if($err)
        {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/signup');
        }
        
        $passwordEncryption = new \App\Models\Auth\PasswordEncryption();
        $passwordHash = $passwordEncryption->encrypt($password);
        
        $this->db->insertRow("INSERT INTO users 
            (username, email, password) 
            VALUES 
            (?, ?, ?)", [
                $username, 
                $email, 
                $passwordHash
            ]);

        $mail = new Mail();
        $mail->send(
                $email, 
                "Welcome to ".$GLOBALS['site_name'], 
                "Hello, $username <br /><br />Thank you for joining us here at ".$GLOBALS['site_name']."! "
                . "You can now use your account to create your personal calendar with your favorite TV Series! <br /><br /> "
                . "The ".$GLOBALS['site_name']." team"
                );
        $login = new \App\Models\Auth\Login();
        $login->login($username, $password, false, \App\Core\CSRF::generate(), md5($_SERVER['HTTP_USER_AGENT']), "http://localhost/MVC/public/");
    }
      
}