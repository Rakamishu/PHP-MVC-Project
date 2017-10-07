<?php

namespace App\Models\Auth;

use \App\Core\Database;
use \App\Core\FlashMessage;

class Login
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
        $this->user_data_from_db = $this->db->getRows("SELECT userid, password, type FROM users WHERE username = ?", [$this->username]);
        
        $this->validate();
        
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
    
    private function validate()
    {
        if(\App\Core\CSRF::check($this->csrf) === false) {
            $err[] = 'CSRF error.';
        }
        
        if(empty($this->username) || empty($this->password)) {
            $err[] = 'All fields are required.';
        }
        
        if($this->user_data_from_db == null) {
            $err[] = 'Username does not exist.';
        }
        
        $passwordEncryption = new \App\Models\Auth\PasswordEncryption();
        if(!empty($this->user_data_from_db[0]->password) && $passwordEncryption->check($this->password, $this->user_data_from_db[0]->password) === false) {
            $err[] = 'Incorrect password.';
        }
        
        if($err) {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/login');
        }
    }
    
    /**
     * Log in with a cookie if a login session is not set but a cookie exists.
     * @param string $cookie - user's login cookie
     * @param string $session - user's login session
     * @param string $agent - HTTP_USER_AGENT
     */
    public function loginWithCookie(string $cookie, $session, string $agent){
        if(!empty($cookie) && empty($session)){
            $this->user_data_from_db = $this->db->getRows("SELECT userid, username, type FROM users WHERE userid = ?", [$this->getUseridFromCookie($cookie)]);
            
            $_SESSION['userid']          = $this->user_data_from_db[0]->userid;
            $_SESSION['cookie_hash']     = $cookie;
            $_SESSION['username']        = $this->user_data_from_db[0]->username;
            $_SESSION['HTTP_USER_AGENT'] = md5($agent);
            $_SESSION['type']            = $this->user_data_from_db[0]->type;

            return redirect("http://localhost/MVC/public/user/login");
        }
    }
    
    /**
     * Gets which user the cookie identifier belongs to.
     * @return int
     */
    public function getUseridFromCookie(string $cookie){
        $user_data_from_db = $this->db->getRow("SELECT userid FROM users_cookies WHERE cookie_hash = ?", [$cookie]);
        return $user_data_from_db->userid;
    }
    
}