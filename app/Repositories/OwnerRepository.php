<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Models\Owner;
use Exception;
use PDOException;

class OwnerRepository
{
  
  public function findByEmail(string $email): Owner|null
  {
    try {
      $sql = "SELECT * FROM owners WHERE email = :email AND deleted_at IS NULL";
      $result = DbConnect::fetch($sql, [':email' => $email]);

      return $result ? new Owner($result) : null;

    } catch(PDOException $e) {
      ErrorHandler::log('オーナー情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('オーナー情報の取得に失敗しました');
    }
  }

  public function insert(Owner $owner): bool
  {
    try {
      $sql = "INSERT INTO owners (name, email, password) values (:name, :email, :password)";
      $param = [
        ':name' => $owner->name, 
        ':email' => $owner->email,
        ':password' => $owner->password];
      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('オーナーの登録に失敗 : ' . $e->getMessage());
      throw new Exception('オーナー登録に失敗しました');
    }
  }

  public function getAll(): array
  {
    try {
      $sql = "SELECT id, name, email, updated_at FROM owners WHERE deleted_at IS NULL";
      return DbConnect::fetchAll($sql);

    } catch(PDOException $e) {
      ErrorHandler::log('オーナー情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('オーナー情報の取得に失敗しました');
    }
  }

  public function findById(int $id): array|null
  {
    try {
      $sql = "SELECT * FROM owners WHERE id = :id AND deleted_at IS NULL";
      $result = DbConnect::fetch($sql, [':id' => $id]);
      
      return $result ?: null;
      
    } catch(PDOException $e) {
      ErrorHandler::log('オーナー情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('オーナー情報の取得に失敗しました');
    }
  }

  public function update(int $id, Owner $owner): bool
  {
    try {
      $sql = "UPDATE owners SET name = :name, email = :email WHERE id = :id";
      $param = [
        ':id' => $id,
        ':name' => $owner->name,
        ':email' => $owner->email
      ];
      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('オーナー情報の変更に失敗 : ' . $e->getMessage());
      throw new Exception('オーナー情報の変更に失敗しました');
    }
  }

  public function delete(int $id): bool
  {
    try {
      $sql = "UPDATE owners SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id";
      return DbConnect::execute($sql, [':id' => $id]);

    } catch(PDOException $e) {
      ErrorHandler::log('オーナー情報の削除に失敗 : ' . $e->getMessage());
      throw new Exception('オーナー情報の削除に失敗しました');
    }
  }


}