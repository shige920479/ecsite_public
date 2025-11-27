<?php
namespace App\Services\Auth;

class LogoutService
{
  public static function logout()
  {
    if(session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    unset($_SESSION['admin'], $_SESSION['owner'], $_SESSION['user']);
    $_SESSION = [];

    if(ini_get("session_use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() -42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
      );
    }
  
    session_destroy();
  }
}