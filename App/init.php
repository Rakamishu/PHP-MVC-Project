<?php

declare(strict_types=1);

error_reporting(E_ALL);

session_start();

require_once "config.php";
require_once "functions/functions.php";

require_once __DIR__ . '/../vendor/autoload.php';


$app = new \App\Core\App;
