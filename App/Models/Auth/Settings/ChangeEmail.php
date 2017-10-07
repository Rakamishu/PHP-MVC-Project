<?php

namespace App\Models\Auth\Settings;

use \App\Models\Auth\PasswordEncryption as PasswordEncryption;
use \App\Core\FlashMessage;

class ChangeEmail
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
    
    public function editEmail()
    {
        $this->validate();
        
        $this->db->updateRow('UPDATE users SET email = ? WHERE userid = ?', [$this->email, $this->userid]);
        $this->updateEmail();
        
        FlashMessage::success('Your email has been changed.');
        redirect(SITE_ADDR.'/public/user/settings/email');
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
        
        if($this->email != $this->email_repeat) {
            $err[] = 'Emails don\'t match';
        }
        
        if(!$this->checkIfEmailIsTaken()) {
            $err[] = 'This email is already being used by another user';
        }
        
        /* Password */
        $passwordEncryption = new PasswordEncryption();
        $get_current_pass = $this->db->getRows("SELECT * FROM users WHERE userid = ?", [$this->userid]);
        if($passwordEncryption->check($this->password, $get_current_pass[0]->password) === false) {
            $err[] = 'Invalid password';
        }
        
        if($err) {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/settings/email');
        }
    }
    
   private function checkIfEmailIsTaken()
    {
        $email_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE email = ?", [$this->email]);
        if($email_unique->count == 0) {
            return true;
        }
        return false;
    }
    
}