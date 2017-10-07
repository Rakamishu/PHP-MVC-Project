<?php

namespace App\Models\Auth\Settings;

use \App\Models\Auth\PasswordEncryption as PasswordEncryption;
use \App\Core\FlashMessage;

class ChangePassword
{    
    
    private $db;
    
    public function __construct($data) 
    {
        $this->db = \App\Core\Database::getInstance();
        foreach($data as $key => $value)
        {
            $this->$key = $value;
        }
    }
    
    public function editPassword()
    {
        $this->validate();
        
        /* Hash the new password before saving to db */
        $passwordEncryption = new PasswordEncryption();
        $this->newpassword_hashed = $passwordEncryption->encrypt($this->newpassword);
        
        $this->db->updateRow('UPDATE users SET password = ? WHERE userid = ?', [$this->newpassword_hashed, $this->userid]);
        FlashMessage::success('Your password has been changed.');
        redirect(SITE_ADDR.'/public/user/settings/password');
    }
    
    private function validate()
    {
        if(\App\Core\CSRF::check($this->csrf) === false)
        {
            $err[] = 'CSRF error.';
        }
        
        /* Password */
        if(strlen($this->newpassword) <= 5)
        {
            $err[] = 'The password is too short.';
        }
        
        if($this->newpassword != $this->newpassword_repeat)
        {
            $err[] = 'Invalid password';
        }
        
        if($this->password == $this->newpassword)
        {
            $err[] = 'New password cannot be the same as your old one.';
        }

        if($this->validate_password() === false)
        {
            $err[] = 'Wrong current password.';
        }
        
        if($err)
        {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/settings/password');
        }
    } 
    
    private function validate_password()
    {
        $curr_password = $this->db->getRow("SELECT password FROM users WHERE userid = ?", [$this->userid]);
        $passwordEncryption = new PasswordEncryption();
        if($passwordEncryption->check($this->password, $curr_password->password) === true)
        {
            return true;
        }
        return false;
    }
    
}

