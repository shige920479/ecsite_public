<?php
if(! defined('BASE_PATH')) define('BASE_PATH', realpath(__DIR__ . '/../'));
require BASE_PATH . '/vendor/autoload.php';
use Carbon\Carbon;

$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

date_default_timezone_set('Asia/Tokyo');
Carbon::setLocale('ja');

require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/config/request.php';
require_once BASE_PATH . '/app/functions/common.php';