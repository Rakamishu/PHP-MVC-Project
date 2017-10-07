<?php

namespace App\Models\Auth\Settings;

use \App\Models\Auth\PasswordEncryption as PasswordEncryption;
use \App\Core\FlashMessage;

class ChangePassword
{    
    
    private $db;
    private $user_data_from_db;
    
    public function __construct($data) 
    {
        $this->db = \App\Core\Database::getInstance();
        foreach($data as $key => $value)
        {
            $this->$key = $value;
        }
        $this->user_data_from_db = $this->db->getRow("SELECT username, password, email FROM users WHERE userid = ?", [$this->userid]);
    }
    
    public function editPassword()
    {
        $this->validate();
        
        /* Hash the new password before saving to db */
        $this->encryptNewPassword();
        
        $this->db->updateRow('UPDATE users SET password = ? WHERE userid = ?', [$this->newpassword_hashed, $this->userid]);
        $this->sendMail();
        FlashMessage::success('Your password has been changed.');
        redirect(SITE_ADDR.'/public/user/settings/password');
    }
    
    private function validate()
    {
        if(\App\Core\CSRF::check($this->csrf) === false) {
            $err[] = 'CSRF error.';
        }
        
        /* Password */
        if(strlen($this->newpassword) <= 5) {
            $err[] = 'The password is too short.';
        }
        
        if($this->newpassword != $this->newpassword_repeat) {
            $err[] = 'Invalid password';
        }
        
        if($this->password == $this->newpassword) {
            $err[] = 'New password cannot be the same as your old one.';
        }

        if($this->validateCurrentPassword() === false) {
            $err[] = 'Wrong current password.';
        }
        
        if(!empty($err)) {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/settings/password');
        }
    } 
    
    private function validateCurrentPassword()
    {
        $passwordEncryption = new PasswordEncryption();
        if($passwordEncryption->check($this->password, $this->user_data_from_db->password) === true)
        {
            return true;
        }
        return false;
    }
    
    private function encryptNewPassword()
    {
        $passwordEncryption = new PasswordEncryption();
        $this->newpassword_hashed = $passwordEncryption->encrypt($this->newpassword);
    }
    
    private function sendMail()
    {
        $mail = new \App\Core\Mail();
        $mail->send(
                $this->user_data_from_db->email, //Receiver
                "Your password changed", // Subject
                "Hello, ".$this->user_data_from_db->username."<br>"
                . "Your password for your account in ".SITE_ADDR." was changed<br>"
                . "If it was you you can safely ignore this email.<br><br>"
                . "if it wasn't you, this means your account has been compromies.<br>"
                . "Please reset your password as soon as possible.<br>"
                );
    }
}

