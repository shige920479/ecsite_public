<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Models\Admin;
use Exception;
use PDOException;

class AdminRepository
{
  public function findByEmail(string $email): Admin|null
  {
    try {
      $sql = "SELECT * FROM admins WHERE email = :email";
      $result = DbConnect::fetch($sql, [':email' => $email]);

      return $result ? new Admin($result) : null;

    } catch(PDOException $e) {
      ErrorHandler::log('管理者情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('管理者情報の取得に失敗しました');
    }
  }
}