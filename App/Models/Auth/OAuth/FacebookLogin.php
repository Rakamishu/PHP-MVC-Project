<?php

namespace App\Models\Auth\OAuth;

use OAuth\OAuth2\Service\Facebook;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;
use OAuth\Common\Http\Uri\Uri;

class FacebookLogin
{
    private $credentials;
    private $serviceCredentials; 
    private $facebookService;
    private $serviceFactory;
    private $storage;
    private $currentUri;
    
    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
        $this->storage = new Session();
        $this->serviceCredentials = $GLOBALS['servicesCredentials'];
        $this->serviceFactory = new ServiceFactory();
        $this->currentUri = new Uri("http://localhost/MVC/public/user/login/facebook");
        
        $this->credentials = new Credentials(
            $this->serviceCredentials['facebook']['key'],
            $this->serviceCredentials['facebook']['secret'],
            $this->currentUri->getAbsoluteUri()
        );
        $this->facebookService = $this->serviceFactory->createService('facebook', $this->credentials, $this->storage, []);
    }
    
    public function getLoginLink()
    {
        if(!isset($_SESSION['fb-login-id'])) {
            header("Location: ". $this->currentUri->getRelativeUri() . '?fb-login=true');
        }
    }
    
    public function getLoginAuth()
    {
        $url = $this->facebookService->getAuthorizationUri();
        header('Location: ' . $url);
    }
    
    public function getLoginResult($state, $code)
    {
        // retrieve the CSRF state parameter
        $state = isset($state) ? $state : null;

        // This was a callback request from facebook, get the token
        $token = $this->facebookService->requestAccessToken($code, $state);

        // Send a request with it
        $this->json_result = json_decode($this->facebookService->request('/me'), true);
        $this->setSessions();
        $existingUser = $this->facebookLoginExists($_SESSION['fb-login-id']);
        if($existingUser === false)
        {
            // Never signed up with FB before - ask user to fill username and email
            redirect("http://localhost/MVC/public/user/signup/facebook");
        }
        else
        {
            $this->login($_SESSION['fb-login-id'], $existingUser);
        }
    }
    
    private function setSessions()
    {
        $_SESSION['fb-login-id'] = $this->json_result['id'];
        $_SESSION['fb-login-name'] = $this->json_result['name'];
    }
    
    /**
     * Check if facebook user has signed up before and return its username or return false
     * @param type $facebook_id
     * @return boolean
     */
    private function facebookLoginExists($facebook_id)
    {
        $data = $this->db->getRow("SELECT username FROM users WHERE facebook_id = ?", [$facebook_id]);
        if(!isset($data->username))
        {
            return false;
        }
        return $data->username;
    }
    
    private function login($facebook_id, $username)
    {
        $login = new \App\Models\Auth\FacebookLogin([
            'username' => $username, 
            'facebook_id' => $facebook_id, 
            'remember_me' => true, 
            'csrf' => \App\Core\CSRF::generate(), 
            'user_agent' => md5($_SERVER['HTTP_USER_AGENT']), 
            'redirect' => SITE_ADDR.'/public'
            ]);
        $login->login();
    }
}