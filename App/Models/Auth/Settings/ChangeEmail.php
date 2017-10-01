<?php

namespace App\Models\Auth\Settings;

use \App\Models\Auth\UserData as Userdata;
use \App\Models\Auth\PasswordEncryption as PasswordEncryption;
use \App\Core\FlashMessage;

class ChangeEmail extends Userdata
{    
    
    protected $db;
    private $email;
    private $email_repeat;
    private $password;
    private $userid;
    private $csrf;
    
    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }
    
    public function editEmail(string $email, string $email_repeat, string $password, int $userid, string $csrf)
    {
        $this->email = $email;
        $this->email_repeat = $email_repeat;
        $this->password = $password;
        $this->userid = $userid;
        $this->csrf = $csrf;
        
        /* Validate the user input */
        if($this->validateInput())
        {
            FlashMessage::error(implode('<br />', $this->validateInput()));
            redirect(SITE_ADDR.'/public/user/settings/email');
        }
        
        $this->updateEmail();
        FlashMessage::success('Your email has been changed.');
        redirect(SITE_ADDR.'/public/user/settings/email');
    }
    
    private function validateInput()
    {
        if(\App\Core\CSRF::check($this->csrf) === false)
        {
            $err[] = 'CSRF error.';
        }
        
        /* Validate Email */
        if(!$this->email = filter_var($this->email, FILTER_SANITIZE_EMAIL))
        {
            $err[] = 'Invalid E-mail address.';
        }
        
        if($this->email != $this->email_repeat)
        {
            $err[] = 'Emails don\'t match';
        }
        
        /* Validate the password */
        $passwordEncryption = new PasswordEncryption();
        if($passwordEncryption->check($this->password, $this->userData($this->userid)[0]->password) === false)
        {
            $err[] = 'Invalid password';
        }
        
        /* Check if the new email isn't taken */
        $email_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE email = ?", [$this->email]);
        if($email_unique->count > 0)
        {
            $err[] = 'This email is already being used by another user';
        }
        
        if(empty($err))
        {
            return false;
        }
        return $err;
    }
    
    public function updateEmail()
    {
        $this->db->updateRow('UPDATE users SET email = ? WHERE userid = ?', [$this->email, $this->userid]);
    }
    
    
}

