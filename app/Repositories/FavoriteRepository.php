<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

class FavoriteRepository
{
  public function isFavorited(int $userId, int $itemId): bool
  {
    try {
      $sql = "SELECT COUNT(id) FROM favorites WHERE user_id = :user_id AND item_id = :item_id";
      $param = [
        'user_id' => $userId,
        'item_id' => $itemId
      ];
      
      return DbConnect::fetchColumn($sql, $param) > 0;

    } catch (PDOException $e) {
      ErrorHandler::log('お気に入りの確認に失敗 : ' . $e->getMessage());
      throw new Exception('お気に入りの確認に失敗しました');
    }
  }

  public function add(int $userId, int $itemId): bool
  {
    try {
      $sql = "INSERT INTO favorites (user_id, item_id) VALUES (:user_id, :item_id)";
      $param = [
        'user_id' => $userId,
        'item_id' => $itemId
      ];
      
      return DbConnect::execute($sql, $param);

    } catch (PDOException $e) {
      ErrorHandler::log('お気に入り情報の登録に失敗 : ' . $e->getMessage());
      throw new Exception('お気に入り情報の登録に失敗しました');
    }
  }

  public function remove(int $userId, int $itemId): bool
  {
    try {
      $sql = "DELETE FROM favorites WHERE user_id = :user_id AND item_id = :item_id";
      $param = [
        'user_id' => $userId,
        'item_id' => $itemId
      ];
      
      return DbConnect::execute($sql, $param);

    } catch (PDOException $e) {
      ErrorHandler::log('お気に入り情報の削除に失敗 : ' . $e->getMessage());
      throw new Exception('お気に入り情報の削除に失敗しました');
    }
  }

  public function deleteById(int $id): bool
  {
    try {
      $sql = "DELETE FROM favorites WHERE id = :id";
      
      return DbConnect::execute($sql, ['id' => $id]);

    } catch (PDOException $e) {
      ErrorHandler::log('お気に入り情報の削除に失敗 : ' . $e->getMessage());
      throw new Exception('お気に入り情報の削除に失敗しました');
    }
  }

  public function getAllByUserId(int $userId): array
  {
    try {
      $sql = "SELECT fa.id AS favorite_id, it.id AS item_id, it.name AS item_name, sh.name AS shop_name,
              it.information AS information, it.price AS price, img.filename AS filename,
              it.is_selling AS is_selling
              FROM favorites AS fa
              JOIN items as it ON fa.item_id = it.id
              JOIN shops AS sh ON it.shop_id = sh.id
              JOIN (SELECT item_id, filename FROM item_images WHERE sort_order = 1) AS img
              ON img.item_id = fa.item_id
              WHERE fa.user_id = :user_id";
      
      return DbConnect::fetchAll($sql, ['user_id' => $userId]);

    } catch(PDOException $e) {
      ErrorHandler::log('お気に入り情報の取得に失敗 :' . $e->getMessage());
      throw new Exception('お気に入り情報の取得に失敗しました');
    }
  }

  public function getById(int $id): array|bool
  {
    try {
      $sql = "SELECT id, user_id, item_id FROM favorites WHERE id = :id";

      return DbConnect::fetch($sql, ['id' => $id]);

    } catch(PDOException $e) {
      ErrorHandler::log('ユーザーIDの取得に失敗' . $e->getMessage());
      throw new Exception('ユーザーIDの取得に失敗しました');
    }

  }

}