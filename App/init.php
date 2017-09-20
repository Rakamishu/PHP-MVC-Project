<?php

session_start();
error_reporting(E_ALL);

require_once "config.php";
require_once "functions/functions.php";
require_once __DIR__ . '/../vendor/autoload.php';

//spl_autoload_register(function($class){
//    if (file_exists(__DIR__ . '/../' . str_replace('\\', '/', $class) .'.php')) {
//        require_once __DIR__ . '/../' . str_replace('\\', '/', $class) .'.php';
//    }
//    if(file_exists('../App/Core/'.$class.'.php'))
//    {
//        require_once '../App/Core/'.$class.'.php';
//    }
//    if(file_exists('../App/Models/'.$class.'.php'))
//    {
//        require_once '../App/Models/'.$class.'.php';
//    }
//});

use App\Core as App;
//use \App\Models\Auth\Login as Login;
//use \App\Core\Database as Database;

$app = new App\App();

/**
 * Checks if login cookie exists and log in the user
 */
//if(isset($_COOKIE['cookie_hash']) && isset($_SESSION['cookie_hash']))
//{
//    $login = new Login(Database::getInstance());
//    $login->cookieLogin($_COOKIE['cookie_hash'], $_SESSION['cookie_hash']);
//}
//
//App\FlashMessage::clear();