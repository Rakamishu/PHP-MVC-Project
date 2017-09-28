<?php

namespace App\Models\Auth;

class PasswordEncryption 
{
    
    public function encrypt($password)
    {
        if(!empty($password))
        {
            $password = md5($password);
            return $password;
        }
    }
    
    
    public function check($clean, $encrypted)
    {
        if(md5($clean) == $encrypted)
        {
            return true;
        }
        return false;
    }
    
}