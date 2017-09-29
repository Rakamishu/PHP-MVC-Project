<?php

declare(strict_types=1);

session_start();
error_reporting(E_ALL);

require_once "config.php";
require_once "functions/functions.php";
require_once __DIR__ . '/../vendor/autoload.php';

use \App\Core as App;
$app = new App\App();
