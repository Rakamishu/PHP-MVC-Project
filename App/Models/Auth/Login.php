<?php

namespace App\Models\Auth;

use \App\Core\Database;
use \App\Models\Auth\PasswordEncryption as PasswordEncryption;
use \App\Core\FlashMessage;
use \DateTime;

class Login
{    
    
    protected $db;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
    }
    
    /** 
     * @param String $username - The username of the user
     * @param String $password - User's password (non hashed)
     * @param bool $rememberMe - If TRUE, set a cookie.
     * @param String $token - Login token that is kept in a cookie if $rememberMe is TRUE.
     * @param String $userAgent - returns md5($_SERVER['HTTP_USER_AGENT'])
     * @param String|Null $redirect - URL to redirect to when login is successful. 
     */
    public function login(string $username, string $password, bool $rememberMe, string $csrf, string $userAgent, string $redirect)
    {
        /**
         * Check against cross-site forgery
         */
        if($csrf != \App\Core\CSRF::check($csrf))
        {
            $err[] = 'CSRF error.';
        }
        
        /**
         * Check if fields are filled.
         */
        if(empty($username) || empty($password))
        {
            $err[] = 'Please fill all fields.';
        }
        
        /**
         * Get data from the database for the requested username.
         */
        $query = $this->db->getRows("SELECT userid, password, type FROM users WHERE username = ?", [$username]);
        if($query == null)
        {
            $err[] = 'Username does not exist.';
        }
        
        /**
         * Compare if the password from the field matches with the password from the database.
         */
        $passwordEncryption = new PasswordEncryption();
        if($passwordEncryption->check($password, $query[0]->password) === false)
        {
            $err[] = 'Incorrect password.';
        }
        
        /**
         * Check if any errors have been registered so far.
         */
        if($err)
        {
            FlashMessage::error(implode('<br />', $err));
            redirect(SITE_ADDR.'/public/user/login');
        }
        
        /**
         * If $rememberMe is set, keep the csrf token as a cookie identifier.
         */
        if($rememberMe)
        {
            $time = new DateTime;
            $expiry_timestamp = $time->getTimestamp() + LOGIN_COOKIE_VALIDITY;
            /**
             * Delete the old cookie identifier from the database and insert a new one.
             */
            $this->db->deleteRow("DELETE FROM users_cookies 
                        WHERE (userid = ?)
                        AND (agent_hash = ? OR expiry < ?)", [
                            $query[0]->userid, 
                            $userAgent, 
                            $time->getTimestamp()
                        ]);
            $this->db->insertRow("INSERT INTO users_cookies 
                        (userid, cookie_hash, agent_hash, expiry) 
                        VALUES 
                        (?, ?, ?, ?)", [
                            $query[0]->userid, 
                            $csrf, 
                            $userAgent, 
                            $expiry_timestamp
                        ]);
            /**
             * Keep the cookie identifier in a cookie.
             */
            setcookie("cookie_hash", $csrf, time() + LOGIN_COOKIE_VALIDITY, '/');
        }

        /**
         * Login the user by setting sessions.
         */
        $_SESSION['userid']          = $query[0]->userid;
        $_SESSION['username']        = $username;
        $_SESSION['HTTP_USER_AGENT'] = $userAgent;
        $_SESSION['type']            = $query[0]->type;

        redirect($redirect);
    }
    
    /**
     * Log in with a cookie if a login session is not set but a cookie exists.
     * @param string $cookie - user's login cookie
     * @param string $session - user's login session
     */
    public function cookieLogin(string $cookie){
        if(!empty($cookie) && empty($_SESSION['username'])){
            $query = $this->db->getRows("SELECT userid, username, type FROM users WHERE userid = ?", [self::getCookieHashUserId()]);
            
            $_SESSION['userid']          = $query[0]->userid;
            $_SESSION['cookie_hash']     = $_COOKIE['cookie_hash'];
            $_SESSION['username']        = $query[0]->username;
            $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
            $_SESSION['type']            = $query[0]->type;

            redirect("http://localhost/MVC/public/user/login");
        }
    }
    
    /**
     * Gets which user the Remember me cookie belongs to
     * @return int
     */
    public function getCookieHashUserId(){
        $query = $this->db->getRow("SELECT `userid` FROM `users_cookies` WHERE `cookie_hash` = ?", [$_COOKIE['cookie_hash']]);
        return $query->userid;
    }
    
}