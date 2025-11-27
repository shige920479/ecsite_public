<?php
namespace App\Controller;

class ErrorController
{
  public function show()
  {
    $error_mode = $_GET['error_mode'] ?? '500error';
    include(APP_PATH . '/Views/Error.php');
  }
}