<?php

namespace App\Models\Auth;

use \App\Core\FlashMessage;
use \App\Core\Mail as Mail;

class PasswordRecovery
{    
    
    private $db;
    private $user_data_from_db;
    private $secret_key;
    private $new_password_readable;
    private $new_password_hashed;
    
    public function __construct($data) 
    {
        $this->db = \App\Core\Database::getInstance();
        foreach($data as $key => $value)
        {
            $this->$key = $value;
        }
        $this->user_data_from_db = $this->db->getRow("SELECT username FROM users WHERE email = ?", [$this->email]);
    }
    
    public function passwordRecovery()
    {
        $this->validate();
        
        $this->generatePass();
                
        $this->db->insertRow("INSERT INTO forgotten_passwords (email, secret_key, new_password, ip) VALUES (?, ?, ?, ?)", [
            $this->email, $this->secret_key, 
            $this->new_password_hashed, $this->ip
            ]);
        
        $this->sendMail($this->secret_key, $this->new_password_readable);
        FlashMessage::info("Check your email.");
        redirect(SITE_ADDR.'/public/user/recovery');
    }
    
    private function validate()
    {
        if(\App\Core\CSRF::check($this->csrf) === false) {
            $err[] = '';
        }
        
        if(empty($this->email)) {
            $err[] = "Please enter a valid E-mail address.";
        }
        
        
        if($this->user_data_from_db == false) {
            $err[] = "This email is not used by anyone.";
        }
        
        if(!empty($err)) {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/recovery');
        }
    }
    
    /**
     *  Generate new password and secret key for activation 
     */
    private function generatePass()
    {
        $random_string = str_shuffle("1234567890QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm!@#$%^&*(-=.");
        $this->secret_key = md5(substr($random_string, 0, 32));
        $this->new_password_readable = substr($random_string, 0, 10);
        /* Hash the new password */
        $passwordEncryption = new \App\Models\Auth\PasswordEncryption();
        $this->new_password_hashed = $passwordEncryption->encrypt($this->new_password_readable);
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
            $this->db->updateRow("
                    UPDATE users
                        SET password = ? 
                        WHERE email = ?;
                    UPDATE forgotten_passwords
                        SET activated = 1 
                        WHERE secret_key = ?", [$validateSecretKey->new_password, $validateSecretKey->email, $secret_key]);
                
            FlashMessage::info("Your password has been updated. Use your new password to log in but don't forget to change it as soon as possible.");
            redirect(SITE_ADDR.'/public/user/login');
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

