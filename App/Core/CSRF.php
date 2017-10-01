<?php

namespace App\Core;

class CSRF 
{
    
    public static function generate()
    {
        $token = bin2hex(openssl_random_pseudo_bytes(32));
        return $_SESSION['csrf'] = $token;
    }
    
    public static function check(string $token)
    {
        if(hash_equals($_SESSION['csrf'], $token))
        {
            return true;            
        }
        return false;     
    }
    
}