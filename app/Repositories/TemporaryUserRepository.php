<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Models\TemporaryUser;
use Exception;
use PDOException;

class TemporaryUserRepository
{
  public function findByEmail(string $email): TemporaryUser|null
  {
    try {
      $sql = "SELECT * FROM temporary_users WHERE email = :email ORDER BY created_at DESC LIMIT 1";
      $result = DbConnect::fetch($sql, [':email' => $email]);
  
      return $result ? new TemporaryUser($result) : null;

    } catch (PDOException $e) {
      ErrorHandler::log('仮ユーザ―情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('仮ユーザー情報の取得に失敗しました');
    }
  }

  public function insert(TemporaryUser $user): bool
  {
    try {
      $sql = "INSERT INTO temporary_users
              (email, verification_code, expires_at)
              VALUES (:email, :verification_code, :expires_at)";
      $param = [
        ':email' => $user->email, 
        ':verification_code' => $user->verification_code,
        ':expires_at' => $user->expires_at];
      
      return DbConnect::execute($sql, $param);

    } catch (PDOException $e) {
      ErrorHandler::log('仮ユーザ―登録に失敗 : ' . $e->getMessage());
      throw new Exception('仮ユーザー登録に失敗しました');
    }
  }
}