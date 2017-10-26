<?php

//define("APP_DEBUG", TRUE);
define("APP_ENV", 'development');

switch(APP_ENV){
    case 'development':
        define("DB_HOST", "127.0.0.1");
        define("DB_USER", "root");
        define("DB_PASSWORD", "");
        define("DB_NAME", "custom_system");
        
        define("GOOGLE_CAPTCHA_SECRET", "6LdngyEUAAAAANQH9WygKfMi8xvCI_cDw5DMpTXI");
        break;
    case 'production':
        
        
        
        break;
}

define('ROOT', __DIR__);

define('SITE_ADDR', 'http://localhost/MVC'); //must not end with /
define('SITE_NAME', 'TV Series Calendar');
define('SITE_KEYWORDS', 'TV Series, TV Shows, Calendar, Schedule, Planner');
define('SITE_DESC', 'Schedule, Follow and Explore your favorite TV Series');

define('LOGIN_COOKIE_VALIDITY', 2592000); // 2592000 seconds = 30 days

$GLOBALS['servicesCredentials'] = [
    'facebook' => [
        'key'       => '509584796053717',
        'secret'    => '9f3da2da310c925ec7d4e7de6ae48d14',
    ]
];