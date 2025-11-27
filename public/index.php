<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap/app.php';

use App\Services\Core\Router;

$env = $_ENV['APP_ENV'] ?? 'production';
if($env === 'production') {
  ini_set('display_errors', '0');
  ini_set('log_errors', '1');
  ini_set('error_log', __DIR__ . '/../app/log/error.log');
} else {
  ini_set('display_errors', '1');
  ini_set('log_errors', '1');
  ini_set('error_log', __DIR__ . '/../app/log/error.log');
  error_reporting(E_ALL);
}

session_start();

$router = new Router();

require BASE_PATH . '/routes/web.php';

if(APP_ENV !== 'production' && file_exists(BASE_PATH . '/routes/dev.php')) {
  require BASE_PATH . '/routes/dev.php';
}

$router->dispatch();