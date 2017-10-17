<?php

use \App\Models\Auth\Authenticate as Authenticate;
use App\Models\Auth\OAuth\FacebookLogin as FacebookLogin;

class User extends \App\Core\Controller
{
    
    private $user_type;
    
    public function __construct() {
        if(isset($_SESSION['type']))
        {
            $this->user_type = $_SESSION['type'];
        }
    }
    
    public function login($type = null)
    {
        /* Check if the user is logged in */
        $auth = new Authenticate($this->user_type);
        if($auth->isUser()) {
            redirect(SITE_ADDR.'/public/home');
        }
        switch($type)
        {
            default: 
                $this->standardLogin();
                break;
            case "facebook":
                $this->facebookLogin();
                break;
        }
    }
    
    private function facebookLogin()
    {
        $facebook_login = new FacebookLogin();
        $facebook_login->getLoginLink();
        if(!empty($_GET['fb-login']) && $_GET['fb-login'] === 'true')
        {
            $facebook_login->getLoginAuth();
        } 
        elseif(!empty($_GET['code']))
        {
            $facebook_login->getLoginResult($_GET['state'], $_GET['code']);
        }
    }
    
    
    private function standardLogin()
    {
        if(isset($_POST['login']))
        {
            $remember_me = isset($_POST['remember_me']) === true ? true : false;
            $users = $this->model('Auth\Login', [
                'username' => $_POST['username'], 
                'password' => $_POST['password'], 
                'remember_me' => $remember_me, 
                'csrf' => $_POST['csrf'], 
                'user_agent' => md5($_SERVER['HTTP_USER_AGENT']), 
                'redirect' => SITE_ADDR.'/public/user/login'
            ]);
            $users->login(); 
        }
        $this->view('header', ['title' => 'Login']);
        $this->view('menu');
        $this->view('users/login', ['csrf' => \App\Core\CSRF::generate()]);
        $this->view('footer');
    }
    
    public function signup($type = null)
    {
        $auth = new Authenticate($this->user_type);
        if($auth->isUser()) {
            redirect(SITE_ADDR.'/public/home');
        }
        switch($type)
        {
            default: 
                $this->normalSignup();
                break;
            case "facebook":
                $this->facebookSignup();
                break;
        }
    }
    
    public function normalSignup()
    {
        if(isset($_POST['signup']))
        {        
            $users = $this->model('Auth\Register', [
                'username' => $_POST['username'], 
                'email' => $_POST['email'], 
                'password' => $_POST['password'], 
                'csrf' => $_POST['csrf'], 
                'recaptcha' => $_POST['g-recaptcha-response'], 
                'ip' => $_SERVER['REMOTE_ADDR'],
            ]);
            $users->register();
        }
        $recaptcha = new ReCaptcha\ReCaptcha(GOOGLE_CAPTCHA);
        $this->view('header', ['title' => 'Register']);
        $this->view('menu');
        $this->view('users/signup', ['recaptcha' => $recaptcha, 'csrf' => \App\Core\CSRF::generate()]);
        $this->view('footer');
    }
    
    public function facebookSignup()
    {
        if(isset($_POST['signup']))
        {        
            $users = $this->model('Auth\FacebookRegister', [
                'username' => $_POST['username'], 
                'email' => $_POST['email'], 
                'csrf' => $_POST['csrf'],
                'facebook_id' => $_SESSION['fb-login-id']
            ]);
            $users->register();
        }
        $this->view('header', ['title' => 'Register']);
        $this->view('menu');
        $this->view('users/oauth_signup', ['csrf' => \App\Core\CSRF::generate()]);
        $this->view('footer');
    }
    
    public function logout()
    {
        setcookie("cookie_hash", '', 0, '/');
        session_destroy();
        
        redirect("http://localhost/MVC/public/user/login");
    }
    
    public function index()
    {
        if(isset($_SESSION['userid']))
        {
            redirect(SITE_ADDR.'/public/user/profile/'.$_SESSION['userid']);
        }
        else 
        {
            error_404();
        }
    }
    
    public function recovery(string $secret_key = null)
    {
        /* Check if the user is logged in */
        $auth = new Authenticate($this->user_type);
        if($auth->isUser())
        {
            redirect(SITE_ADDR.'/public/home');
        }
        
        /* If secret key is set, proceed to activating the new password */
        if($secret_key != null)
        {
            $users = $this->model('Auth\PasswordRecovery');
            $users->activatePass($secret_key);
        }
        
        /* If secret key is empty, show the form for requesting a password change. */
        if(isset($_POST['recover']))
        {
            $users = $this->model('Auth\PasswordRecovery', ['email' => $_POST['email'], 'ip' => $_SERVER['REMOTE_ADDR'], 'csrf' => $_POST['csrf']]);
            $users->passwordRecovery();
        }
        
        $this->view('header', ['title' => 'Password Recovery']);
        $this->view('menu');
        $this->view('users/recovery', ['csrf' => \App\Core\CSRF::generate()]);
        $this->view('footer');
    }
    
    
    public function profile(int $userid = null)
    {        
        $users = $this->model('Auth\Profile', ['id' => $userid]);
        $data = $users->profile();
        if(!$data) {
            error_404();
        }
        
        $this->view('header', ['title' => 'Profile for user']);
        $this->view('menu');
        $this->view('users/view', $data);
        $this->view('footer'); 
    }
    
    public function settings(string $settings)
    {
        /* Check if the user is logged in */
        $auth = new Authenticate($this->user_type);
        if(!$auth->isUser()) {
            redirect(SITE_ADDR.'/public/home');
        }
        
        switch ($settings)
        {
            default: 
                error_404(); 
                break;
            
            case "email":
                $this->changeEmail();
                break;
            
            case "password":
                $this->changePassword();  
                break;
        }
    }
    
    private function changeEmail()
    {
        if(isset($_POST['update']))
        {
            $users = $this->model('Auth\Settings\ChangeEmail', [
                'email' => $_POST['email'], 
                'email_repeat' => $_POST['email_repeat'], 
                'password' => $_POST['password'], 
                'userid' => $_SESSION['userid'], 
                'csrf' => $_POST['csrf']
            ]);
            $users->editEmail();
        }
        $this->view('header', ['title' => 'Update Email']);
        $this->view('menu');
        $this->view('users/edit_email', ['csrf' => \App\Core\CSRF::generate()]);
        $this->view('footer');
    }
    
    private function changePassword()
    {
        if(isset($_POST['update']))
        {    
            $users = $this->model('Auth\Settings\ChangePassword', [
                'userid' => $_SESSION['userid'], 
                'password' => $_POST['password'], 
                'newpassword' => $_POST['newpassword'], 
                'newpassword_repeat' => $_POST['newpassword_repeat'], 
                'csrf' => $_POST['csrf']
            ]);
            $users->editPassword();
        }  
        $this->view('header', ['title' => 'Update Password']);
        $this->view('menu');
        $this->view('users/edit_password', ['csrf' => \App\Core\CSRF::generate()]);
        $this->view('footer');
    }
    
}
