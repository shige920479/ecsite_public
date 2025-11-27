<?php
namespace App\Services\Security;

class TokenManager
{
  public static function generateToken(): string
  {
    return bin2hex(random_bytes(32));
  }

  public static function get(): string
  {
      if (! isset($_SESSION['token'])) {
          $_SESSION['token'] = self::generateToken();  // ← 必要なときだけ生成
      }
      return $_SESSION['token'];
  }

  public static function destroy(): void
  {
    unset($_SESSION['token']);
  }
}