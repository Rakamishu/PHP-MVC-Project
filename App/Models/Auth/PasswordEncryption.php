<?php

namespace App\Models\Auth;

class PasswordEncryption 
{
    
    public function encrypt(string $password)
    {
        if(!empty($password))
        {
            $password = md5($password);
            return $password;
        }
    }
    
    
    public function check(string $clean, string $encrypted)
    {
        if(md5($clean) == $encrypted)
        {
            return true;
        }
        return false;
    }
    
}