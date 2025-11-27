<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Models\Shop;
use Exception;
use PDOException;

class ShopRepository
{


  public function insert(array $request)
  {
    try {
      $sql = "INSERT INTO shops
              (owner_id, name, information, filename, is_selling)
              VALUES
              (:owner_id, :name, :information, :filename, :is_selling)";
      $param = [
        ':owner_id' => $request['owner_id'],
        ':name' => $request['name'],
        ':information' => $request['information'],
        ':filename' => $request['filename'],
        ':is_selling' => $request['is_selling']
      ];

      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('ショップ登録失敗 : ' . $e->getMessage());
      throw new Exception('ショップ登録に失敗しました');
    }
  }

  public function update(int $id, array $request)
  {
    try {
      $sql = "UPDATE shops 
              SET name = :name, information = :information, is_selling = :is_selling, filename = :filename
              WHERE id = :id";
      $param = [
        ':name' => $request['name'],
        ':information' => $request['information'],
        ':is_selling' => $request['is_selling'],
        ':filename' => $request['filename'],
        ':id' => $id
      ];

      DbConnect::execute($sql, $param); // 失敗時はfalse
      return $this->getById($id);

    } catch(PDOException $e) {
      ErrorHandler::log('ショップ情報の更新失敗 : ' . $e->getMessage());
      throw new Exception('ショップ情報の更新に失敗しました');
    }
  }

  public function getByOwnerId(int $id)
  {
    try {
      $sql = "SELECT id, name, information, filename, is_selling FROM shops
              WHERE owner_id = :owner_id AND deleted_at IS NULL";
      $result = DbConnect::fetch($sql, [':owner_id' => $id]);

      return $result ? new Shop($result) : null;

    } catch(PDOException $e) {
      ErrorHandler::log('ショップ情報取得に失敗 : ' . $e->getMessage());
      throw new Exception('ショップ情報の取得に失敗しました');

    }
  }
  public function getById(int $id)
  {
    try {
      $sql = "SELECT id, owner_id, name, information, filename, is_selling FROM shops
              WHERE id = :id AND deleted_at IS NULL";
      $result = DbConnect::fetch($sql, [':id' => $id]);

      return $result ? new Shop($result) : null;

    } catch(PDOException $e) {
      ErrorHandler::log('ショップ情報取得に失敗 : ' . $e->getMessage());
      throw new Exception('ショップ情報の取得に失敗しました');
    }
  }
  public function getNameById(int $id)
  {
    try {
      $sql = "SELECT name FROM shops
              WHERE id = :id AND deleted_at IS NULL";
      $result = DbConnect::fetch($sql, [':id' => $id]);

      return $result ? $result['name'] : null;

    } catch(PDOException $e) {
      ErrorHandler::log('ショップ名の取得に失敗 : ' . $e->getMessage());
      throw new Exception('ショップ名の取得に失敗しました');
    }
  }
}