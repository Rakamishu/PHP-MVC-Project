<?php


namespace App\Models\Auth;

use App\Core\Database;
use PHPMailer\PHPMailer\Exception;

class CookieLogin
{

    private $db;
    private $user_data_from_db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log in with a cookie if a login session is not set but a cookie exists.
     * @param string $cookie - user's login cookie
     * @param string $username_session - user's login session
     * @param string $agent - HTTP_USER_AGENT
     */
    public function loginWithCookie(string $cookie, $username_session = null, string $agent)
    {
        if(!empty($cookie) && empty($username_session)){
            $this->user_data_from_db = $this->db->getRows("SELECT userid, username, type FROM users WHERE userid = ?", [$this->getUserIdFromCookie($cookie)]);
            if($this->user_data_from_db == null)
            {
                //the user for the set cookie doesn't exist or is deleted. redirect to logout to clear all cookies and sessions.
                redirect(SITE_ADDR.'/public/user/logout');
            }

            $_SESSION['userid']          = $this->user_data_from_db[0]->userid;
            $_SESSION['cookie_hash']     = $cookie;
            $_SESSION['username']        = $this->user_data_from_db[0]->username;
            $_SESSION['HTTP_USER_AGENT'] = md5($agent);
            $_SESSION['type']            = $this->user_data_from_db[0]->type;

            return redirect(SITE_ADDR.'/public/user/login');
        }
    }

    /**
     * Determines which user the cookie identifier belongs to.
     * @return int
     */
    public function getUserIdFromCookie(string $cookie)
    {
        try {
            $user_data_from_db = $this->db->getRow("SELECT userid FROM users_cookies WHERE cookie_hash = ?", [$cookie]);
            return $user_data_from_db->userid;
        } catch (\Exception $ex) {
            redirect(SITE_ADDR."/public/user/logout");
            throw new Exception("Username does not exist. ".$ex->getMessage());
        }
    }

}