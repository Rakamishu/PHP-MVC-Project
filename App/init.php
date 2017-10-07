<?php

error_reporting(E_ALL | E_STRICT);

session_start();

require_once "config.php";
require_once "functions/functions.php";

require_once __DIR__ . '/../vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$app = new \App\Core\App;
