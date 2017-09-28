<?php

namespace App\Models\Auth;

use \App\Models\Auth\PasswordEncryption as PasswordEncryption;
use \App\Core\FlashMessage;
use \App\Core\Mail as Mail;

class PasswordRecovery
{    
    
    protected $db;
    
    public function __construct() 
    {
        $this->db = \App\Core\Database::getInstance();
    }
    
    public function passwordRecovery($email, $ip, $csrf)
    {
        if($csrf != \App\Core\CSRF::check($csrf))
        {
            $err[] = '';
        }
        
        if(empty($email))
        {
            $err[] = "Please enter a valid E-mail address.";
        }
        
        $userinfo = $this->db->getRow("SELECT username FROM users WHERE email = ?", [$email]);
        if($userinfo == false)
        {
            $err[] = "This email is not used by anyone.";
        }
        
        /**
         * Check if any errors have been registered so far, otherwise proceed to sending a new password
         */
        if($err)
        {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/recovery');
        }
        
        /**
         * Generate new secret key for activation.
         * Generate new password that is readable.
         * Hash the generated password.
         */
        $random_string = str_shuffle("1234567890QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm!@#$%^&*(-=.");
        $secret_key = md5(substr($random_string, 0, 32));
        $new_password_readable = substr($random_string, 0, 10);
        
        $passwordEncryption = new PasswordEncryption();
        $new_password = $passwordEncryption->encrypt($new_password_readable);
        
        $this->db->insertRow("INSERT INTO forgotten_passwords 
            (email, secret_key, new_password, ip) 
            VALUES 
            (?, ?, ?, ?)", [$email, $secret_key, $new_password, $ip]);
        
        $mail = new Mail();
        $sendMail = $mail->send(
                $email, //Receiver
                "New Password requested", // Subject
                "Hello, ".$userinfo->username."<br>"
                . "We are sending you a new password for your account in ".SITE_ADDR."<br>"
                . "If it wasn't you who requested a new password, ignore the rest of this E-mail.<br><br>"
                . "We generated a new password for you: <b>".$new_password_readable."</b><br>"
                . "To activate it, click <a href='".SITE_ADDR."/public/user/recovery/".$secret_key."'>here</a><br>"
                . "The activation link will be active for the next 24 hours.<br>"
                . "Don't forget to change your password with a better, more secure one, as soon as possible.<br>"
                . "The request came from IP: ".$ip
                );
        
        if($sendMail)
        {
            FlashMessage::info("Check your email.");
            redirect(SITE_ADDR.'/public/user/recovery');
        }
    }
    
    
    public function activate($secret_key){
        $validate_secret = $this->db->getRow("SELECT * FROM forgotten_passwords WHERE secret_key = ?", [$secret_key]);
        
        if($validate_secret != false && strtotime($validate_secret->date_generated)+60*60*24 > time() && $validate_secret->activated < 1)
        {
            try {
                $this->db->updateRow("
                        UPDATE users
                            SET password = ? 
                            WHERE email = ?;
                        UPDATE forgotten_passwords
                            SET activated = 1 
                            WHERE secret_key = ?", [$validate_secret->new_password, $validate_secret->email, $secret_key]);
                
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
    
}

