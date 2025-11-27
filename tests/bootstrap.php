<?php

use Dotenv\Dotenv;

if(! defined('BASE_PATH')) define('BASE_PATH', realpath(__DIR__ . '/../'));
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(BASE_PATH);
$dotenv->load();
require_once __DIR__ . '/../app/config/config.php';

