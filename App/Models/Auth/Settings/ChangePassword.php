<?php

namespace App\Models\Auth\Settings;

use \App\Models\Auth\PasswordEncryption as PasswordEncryption;
use \App\Core\FlashMessage;

class ChangePassword
{    
    
    protected $db;
    private $userid;
    private $password;
    private $newpassword;
    private $newpassword_hashed;
    private $newpassword_repeat;
    private $csrf;
    
    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }
    
    public function editPassword(int $userid, string $password, string $newpassword, string $newpassword_repeat, string $csrf)
    {
        $this->userid = $userid;
        $this->password = $password;
        $this->newpassword = $newpassword;
        /* Save a hashed version of the new password */
        $passwordEncryption = new PasswordEncryption();
        $this->newpassword_hashed = $passwordEncryption->encrypt($this->newpassword);
        $this->newpassword_repeat = $newpassword_repeat;
        $this->csrf = $csrf;
        
        /* Validate the user input */
        if($this->validateInput())
        {
            FlashMessage::error(implode('<br />', $this->validateInput()));
            redirect(SITE_ADDR.'/public/user/settings/password');
        }
        
        $this->updatePassword();
        FlashMessage::success('Your password has been changed.');
        redirect(SITE_ADDR.'/public/user/settings/password');
    }
    
    private function validateInput()
    {
        if(\App\Core\CSRF::check($this->csrf) === false)
        {
            $err[] = 'CSRF error.';
        }
        
        /* Check if the password is too short. */
        if(strlen($this->newpassword) <= 5)
        {
            $err[] = 'The password is too short.';
        }
        
        /* Verify that password and password_repeat match */
        if($this->newpassword != $this->newpassword_repeat)
        {
            $err[] = 'Invalid password';
        }
        
        /* Verify the new password is not the same as the old one */
        if($this->password == $this->newpassword)
        {
            $err[] = 'New password cannot be the same as your old one.';
        }
        
        /* Verify if the current password is correct */
        $curr_password = $this->db->getRow("SELECT password FROM users WHERE userid = ?", [$this->userid]);
        $passwordEncryption = new PasswordEncryption();
        if($passwordEncryption->check($this->password, $curr_password->password) === false)
        {
            $err[] = 'Wrong current password.';
        }
        
        return empty($err) ? false : $err;
    }
    
    
    public function updatePassword()
    {
        $this->db->updateRow('UPDATE users SET password = ? WHERE userid = ?', [$this->newpassword_hashed, $this->userid]);
    }
    
}

