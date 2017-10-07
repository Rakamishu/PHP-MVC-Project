<?php

namespace App\Models\Auth;

use \App\Core\FlashMessage;
use \App\Core\Mail as Mail;

class PasswordRecovery
{    
    
    protected $db;
    private $email;
    private $ip;
    private $csrf;
    private $user_data_from_db;
    
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
    
    public function passwordRecovery()
    {
        /* Get the username from the database */
        $this->user_data_from_db = $this->db->getRow("SELECT username FROM users WHERE email = ?", [$this->email]);
        
        /* Validate the user input */
        if($this->validate())
        {
            FlashMessage::error(implode('<br />', $this->validate()));
            redirect(SITE_ADDR.'/public/user/recovery');
        }
        
        /* Generate new password and return it both non-hashed and hashed. */
        $generatePass = $this->generatePass();
        $passwordEncryption = new \App\Models\Auth\PasswordEncryption();
        $new_password = $passwordEncryption->encrypt($generatePass['new_password_readable']);
        
        $this->db->insertRow("INSERT INTO forgotten_passwords 
            (email, secret_key, new_password, ip) 
            VALUES 
            (?, ?, ?, ?)", [$this->email, $generatePass['secret_key'], $new_password, $this->ip]);
        
        $sendMail = $this->sendMail($generatePass['secret_key'], $generatePass['new_password_readable']);
        if($sendMail)
        {
            FlashMessage::info("Check your email.");
            redirect(SITE_ADDR.'/public/user/recovery');
        }
    }
    
    private function validate()
    {
        if(\App\Core\CSRF::check($this->csrf) === false)
        {
            $err[] = '';
        }
        
        if(empty($this->email))
        {
            $err[] = "Please enter a valid E-mail address.";
        }
        
        
        if($this->user_data_from_db == false)
        {
            $err[] = "This email is not used by anyone.";
        }
        
        return empty($err) ? false : $err;
    }
    
    private function generatePass()
    {
        $random_string = str_shuffle("1234567890QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm!@#$%^&*(-=.");
        $secret_key = md5(substr($random_string, 0, 32));
        $new_password_readable = substr($random_string, 0, 10);
        return ['secret_key' => $secret_key, 'new_password_readable' => $new_password_readable];
    }
    
    private function sendMail(string $secret_key, string $new_password_readable)
    {
        $mail = new Mail();
        $mail->send(
                $this->email, //Receiver
                "New Password requested", // Subject
                "Hello, ".$this->user_data_from_db->username."<br>"
                . "We are sending you a new password for your account in ".SITE_ADDR."<br>"
                . "If it wasn't you who requested a new password, ignore the rest of this E-mail.<br><br>"
                . "We generated a new password for you: <b>".$new_password_readable."</b><br>"
                . "To activate it, click <a href='".SITE_ADDR."/public/user/recovery/".$secret_key."'>here</a><br>"
                . "The activation link will be active for the next 24 hours.<br>"
                . "Don't forget to change your password with a better, more secure one, as soon as possible.<br>"
                . "The request came from IP: ".$this->ip
                );
        return true;
    }
    
    /**
     * Update user's password with the previously generated one 
     * @param string $secret_key 
     */
    public function activatePass(string $secret_key)
    {
        $validateSecretKey = $this->validateSecretKey($secret_key);
        if($validateSecretKey)
        {
            try {
                $this->db->updateRow("
                        UPDATE users
                            SET password = ? 
                            WHERE email = ?;
                        UPDATE forgotten_passwords
                            SET activated = 1 
                            WHERE secret_key = ?", [$validateSecretKey->new_password, $validateSecretKey->email, $secret_key]);
                
                FlashMessage::info("Your password has been updated. Use your new password to log in but don't forget to change it as soon as possible.");
                redirect(SITE_ADDR.'/public/user/login');
            } catch (Exception $ex) {
                echo $ex;
            }
        } else {
            FlashMessage::info("Invalid or expired code for activation.");
            redirect(SITE_ADDR.'/public/user/recovery');
        }
    }
    
    private function validateSecretKey(string $secret_key)
    {
        $validate_secret = $this->db->getRow("SELECT * FROM forgotten_passwords WHERE secret_key = ?", [$secret_key]);
        
        if($validate_secret && strtotime($validate_secret->date_generated)+60*60*24 > time() && $validate_secret->activated < 1)
        {
            return $validate_secret;
        }
        return false;
    }
    
}

