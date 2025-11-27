<?php
namespace App\Exceptions;

use Carbon\Carbon;

class ErrorHandler
{
  //test用に追加
  public static $redirectCallback = null;
  public static $logCallback = null;

  public static function redirectWithCode(int $errorCode): void
  {
    //test用に追加
    if(is_callable(self::$redirectCallback)) {
      call_user_func(self::$redirectCallback, $errorCode);
      return;
    }

    header('Location:' . PATH . '/error?error_mode=' . urlencode($errorCode) . 'error');
    exit;
  }

  public static function log(string $message): void
  {
    if(is_callable(self::$logCallback)) {
      call_user_func(self::$logCallback, $message);
      return;
    }

    $now = Carbon::now()->format('Y-m-d H:i:s');
    error_log('[ErrorHandler]'. $now . $message . "\n", 3, APP_PATH . '/log/error.log');
  }

  public static function respondJsonError(string $message = '不正なリクエスト', int $status = 400): void
  {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
  }
}