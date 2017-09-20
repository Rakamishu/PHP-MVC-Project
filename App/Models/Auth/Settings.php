<?php

namespace App\Models\Auth;

use App\Models\Auth\UserData as Userdata;
use App\Core\Database;
use App\Core\FlashMessage;
use \DateTime;

class Settings extends Userdata
{    
    
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function editEmail($email, $email_repeat, $password, $userid)
    {
        /**
         * Check for empty fields
         */
        if(empty($email) || (empty($email_repeat)) || (empty($password)))
        {
            $err[] = 'All fields are required';
        }
        
        /**
         * Check if the new email is valid
         */
        if(!$email = filter_var($email, FILTER_SANITIZE_EMAIL))
        {
            $err[] = 'Invalid E-mail address.';
        }
        
        /**
         * Check if both emails match
         */
        if($email != $email_repeat)
        {
            $err[] = 'Emails don\'t match';
        }
        
        /**
         * Hash the entered password and compare it with the existing one.
         */
        $passwordHasher = new \App\Models\Auth\Register();
        $password = $passwordHasher->passwordHash($password);
        if($this->userData($userid)[0]->password != $password)
        {
            $err[] = 'Invalid password';
        }
        
        /**
         * Check if the new email is not taken by another user
         */
        $email_existance = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE email = ?", [$email]);
        if($email_existance->count > 0)
        {
            $err[] = 'This email is already being used by another user';
        }
        
        /**
         * Check if any errors have been registered so far, otherwise proceed to registering the user
         */
        if($err)
        {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/settings/email');
        }
        
        $this->db->updateRow('UPDATE users SET email = ? WHERE userid = ?', [$email, $userid]);
        FlashMessage::success('Your email has been changed.');
        redirect(SITE_ADDR.'/public/user/settings/email');
    }
    
    public function editPassword()
    {
        FlashMessage::error("Changes take place here");
        redirect(SITE_ADDR.'/public/user/settings/password');
    }
    
}

