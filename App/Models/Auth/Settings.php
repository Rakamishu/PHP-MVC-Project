<?php

namespace App\Models\Auth;

use App\Models\Auth\UserData as Userdata;
use App\Models\Auth\PasswordEncryption as PasswordEncryption;
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
         * Check if the new email is valid
         */
        if(!$email = filter_var($email, FILTER_SANITIZE_EMAIL))
        //$validator = new \EmailValidator\Validator();
        //if($validator->isEmail($email) == false)
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
         * Verify the password
         */
        $passwordEncryption = new PasswordEncryption();
        if($passwordEncryption->check($password, $this->userData($userid)[0]->password) === false)
        {
            $err[] = 'Invalid password';
        }
        
        /**
         * Check if the new email is not taken by another user
         */
        $email_unique = $this->db->getRow("SELECT COUNT(*) as count FROM users WHERE email = ?", [$email]);
        if($email_unique->count > 0)
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
    
    public function editPassword($userid, $password, $newpassword, $newpassword_repeat, $csrf)
    {
        if($csrf != \App\Core\CSRF::check($csrf))
        {
            $err[] = 'CSRF error.';
        }
        
        /**
         * Check if the password is too short.
         */
        if(strlen($newpassword) <= 5)
        {
            $err[] = 'The password is too short.';
        }
        
        /**
         * Verify that password and password_repeat match
         */
        if($newpassword != $newpassword_repeat)
        {
            $err[] = 'Invalid password';
        }
        
        /**
         * Verify the new password is not the same as the old one
         */
        if($password == $newpassword)
        {
            $err[] = 'New password cannot be the same as your old one.';
        }
        
        /**
         * Verify if the current password is correct
         */
        $curr_password = $this->db->getRow("SELECT password FROM users WHERE userid = ?", [$userid]);
        $passwordEncryption = new PasswordEncryption();
        if($passwordEncryption->check($password, $curr_password->password) === false)
        {
            $err[] = 'Wrong current password.';
        }
        
        /**
         * Check if any errors have been registered so far, otherwise proceed to registering the user
         */
        if($err)
        {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/settings/password');
        }
        
        $newpassword = $passwordEncryption->encrypt($newpassword);
        
        $this->db->updateRow('UPDATE users SET password = ? WHERE userid = ?', [$newpassword, $userid]);
        FlashMessage::success('Your password has been changed.');
        redirect(SITE_ADDR.'/public/user/settings/password');
    }
    
}

