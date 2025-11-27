<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Models\User;
use Exception;
use PDOException;

class UserRepository
{
  public function findByEmail(string $email): User|null
  {
    try {
      $sql = "SELECT * FROM users WHERE email = :email";
      $result = DbConnect::fetch($sql, [':email' => $email]);
  
      return $result ? new User($result) : null;

    } catch(PDOException $e) {
      ErrorHandler::log('ユーザー情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('ユーザー情報の取得に失敗しました');
    }
  }

  public function insert(User $user): bool
  {
    try {
      $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
      $param = [
        ':name' => $user->name, 
        ':email' => $user->email,
        ':password' => $user->password];
      
      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('ユーザー登録に失敗 : ' . $e->getMessage());
      throw new Exception('ユーザー登録に失敗しました');
    }
  }
}