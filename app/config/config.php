<?php
if(! defined('APP_ENV')) define('APP_ENV', $_ENV['APP_ENV']);

if(APP_ENV === 'production') {
  if(! defined('BASE_URL')) define('BASE_URL', '/ecsite/');
  if(! defined('DB_HOST')) define('DB_HOST', 'localhost');
} else {
  if(! defined('BASE_URL')) define('BASE_URL', '/');
  if(! defined('DB_HOST')) define('DB_HOST', 'db');
}

if(! defined('PATH')) define('PATH', '/ecsite');
if(! defined('APP_PATH')) define('APP_PATH', BASE_PATH . '/app');
if(! defined('PUBLIC_PATH')) define('PUBLIC_PATH', BASE_PATH . '/public');
if(! defined('UPLOAD_PATH')) define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
if(! defined('TMP_PATH')) define('TMP_PATH', PUBLIC_PATH . '/tmp');

if(! defined('DB_NAME')) define('DB_NAME', $_ENV['MYSQL_DATABASE']);
if(! defined('DB_USER')) define('DB_USER', $_ENV["MYSQL_USER"]);
if(! defined('DB_PASS')) define('DB_PASS', $_ENV["MYSQL_PASSWORD"]);
if(! defined('IMG_MAX')) define('IMG_MAX', 1024);
if(! defined('PER_PAGE_OPTION')) define('PER_PAGE_OPTION', [8, 12, 16]);
if(! defined('TAX_RATE')) define('TAX_RATE', 0.1);
if(! defined('ADD_STOCK')) define('ADD_STOCK', 'add');
if(! defined('REDUCE_STOCK')) define('REDUCE_STOCK', 'reduce');

if(! defined('STRIPE_PUBLIC_KEY')) define('STRIPE_PUBLIC_KEY', $_ENV["STRIPE_PUBLIC_KEY"]);
if(! defined('STRIPE_SECRET_KEY')) define('STRIPE_SECRET_KEY', $_ENV["STRIPE_SECRET_KEY"]);
if(! defined('STRIPE_WEBHOOK_SECRET')) define('STRIPE_WEBHOOK_SECRET', $_ENV['STRIPE_WEBHOOK_SECRET']);

if(! defined('MAIL_HOST')) define('MAIL_HOST', $_ENV["MAIL_HOST"]);
if(! defined('MAIL_PORT')) define('MAIL_PORT', $_ENV["MAIL_PORT"]);
if(! defined('MAIL_USERNAME')) define('MAIL_USERNAME', $_ENV["MAIL_USERNAME"]);
if(! defined('MAIL_PASSWORD')) define('MAIL_PASSWORD', $_ENV["MAIL_PASSWORD"]);
if(! defined('MAIL_FROM')) define('MAIL_FROM', $_ENV["MAIL_FROM"]);
if(! defined('MAIL_FROM_NAME')) define('MAIL_FROM_NAME', $_ENV["MAIL_FROM_NAME"]);
if(! defined('ADMIN_MAIL')) define('ADMIN_MAIL', $_ENV["ADMIN_MAIL"]);
if(! defined('ADMIN_NAME')) define('ADMIN_NAME', $_ENV["ADMIN_NAME"]);
if(! defined('DEMO_RESET_ENABLED')) define('DEMO_RESET_ENABLED', $_ENV["DEMO_RESET_ENABLED"]);

if(! defined('SLACK_WEBHOOK_URL')) define('SLACK_WEBHOOK_URL', $_ENV["SLACK_WEBHOOK_URL"]);