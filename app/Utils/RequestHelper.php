<?php
namespace App\Utils;

class RequestHelper
{
  public static function isAjax(): bool
  {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
  }

  public static function isAjaxOrApi(): bool
  {
    return self::isAjax() ||
          (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
  }
}
