<?php
namespace App\Services\Security;

class PasswordService
{
  /**
   * パスワードハッシュ化
   */
  public static function hashPassword(String $password): string
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }
  /**
   * パスワードの照合
   */
  public static function verifyPassword(string $password, $hash): bool
  {
    return password_verify($password, $hash);
  }

}