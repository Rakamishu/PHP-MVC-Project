<?php

namespace App\Models\Auth;

use \App\Core\Database;

class FacebookLogin
{    
    
    private $db;
    private $user_data_from_db;

    public function __construct($data = null) 
    {
        $this->db = Database::getInstance();
        if(isset($data))
        {
            foreach($data as $key => $value)
            {
                $this->$key = $value;
            }
        }
    }
    
    public function login()
    {
        /* Get user password from the database. */
        $this->user_data_from_db = $this->db->getRows("SELECT userid, password, type FROM users WHERE facebook_id = ?", [$this->facebook_id]);
        
        if($this->remember_me)
        {
            $this->setLoginCookie();
        }
        
        /* Sets the login sessions */
        $this->setLoginSession();
        
        redirect($this->redirect);
    }
    
    private function setLoginSession()
    {
        $_SESSION['userid']          = $this->user_data_from_db[0]->userid;
        $_SESSION['username']        = $this->username;
        $_SESSION['HTTP_USER_AGENT'] = $this->user_agent;
        $_SESSION['type']            = $this->user_data_from_db[0]->type;
    }
    
    /**
     * Save the csrf token in a cookie as a identifier. Update the user table with the new cookie identifier.
     */
    private function setLoginCookie()
    {
        $time = new \DateTime;
        $expiry_timestamp = $time->getTimestamp() + LOGIN_COOKIE_VALIDITY;
        /* Delete the old cookie identifier from the database. */
        $this->db->deleteRow("DELETE FROM users_cookies WHERE (userid = ?) AND (agent_hash = ? OR expiry < ?)", [
            $this->user_data_from_db[0]->userid, 
            $this->user_agent, 
            $time->getTimestamp()
            ]);
        /* Insert the new one */
        $this->db->insertRow("INSERT INTO users_cookies (userid, cookie_hash, agent_hash, expiry) VALUES (?, ?, ?, ?)", [
            $this->user_data_from_db[0]->userid, 
            $this->csrf, 
            $this->user_agent, 
            $expiry_timestamp
            ]);
        /* Keep the csrf token in the cookie as identifier. */
        setcookie("cookie_hash", $this->csrf, time() + LOGIN_COOKIE_VALIDITY, '/');
    }
    
}