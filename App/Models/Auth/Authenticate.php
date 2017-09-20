<?php

namespace App\Models\Auth;

class Authenticate
{    
    
    private $type = null;
    
    public function __construct($type) {
        if($type)
        {
            $this->type = $type;
        }
    }
    
    public function isUser()
    {
        if(isset($this->type))
        {
            return true;            
        }        
        return false;
    }
    
    public function isAdmin()
    {
        if(isset($this->type) && $this->type == 3)
        {
            return true;            
        }        
        return false;
    }
    
    public function isModerator()
    {
        if(isset($this->type) && $this->type == 2)
        {
            return true;            
        }        
        return false;
    }
    
}