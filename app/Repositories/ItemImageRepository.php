<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

class ItemImageRepository
{
  public function insert(array $imageData)
  {
    try {
      $sql = "INSERT INTO item_images
              (item_id, filename, sort_order)
              VALUES
              (:item_id, :filename, :sort_order)";
      $param = [
        ':item_id' => $imageData['item_id'],
        ':filename' => $imageData['filename'],
        ':sort_order' => $imageData['sort_order']
      ];

      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('画像登録に失敗 : ' . $e->getMessage());
      throw new Exception('画像登録に失敗しました');
    }
  }

  public function update(array $imageData): bool
  {
    try {
      $sql = "UPDATE item_images SET filename = :filename, sort_order = :sort_order WHERE id = :id";
      
      return DbConnect::execute($sql, [
        ':filename' => $imageData['filename'],
        ':sort_order' => $imageData['sort_order'],
        ':id' => $imageData['id']
      ]);

    } catch(PDOException $e) {
      ErrorHandler::log('商品画像の更新に失敗 : ' . $e->getMessage());
      throw new Exception('商品画像の更新に失敗しました');
    }
  }

  public function updateSortOrder(array $imageData): bool
  {
    try {
      $sql = "UPDATE item_images SET sort_order = :sort_order WHERE id = :id";
      $param = [
        ':sort_order' => $imageData['sort_order'],
        ':id' => $imageData['id']
      ];
      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('商品画像のソート変更に失敗 : ' . $e->getMessage());
      throw new Exception('商品画像のソート変更に失敗しました');
    }
  }

  public function delete(int $image_id): bool
  {
    try {
      $sql = "DELETE FROM item_images WHERE id = :id";

      return DbConnect::execute($sql, [':id' => $image_id]);

    } catch(PDOException $e) {
      ErrorHandler::log('商品画像の削除に失敗 : ' . $e->getMessage());
      throw new Exception('商品画像の削除に失敗しました');
    }
  }

  public function deleteByItemId(int $itemId): bool
  {
    try {
      $sql = "DELETE FROM item_images WHERE item_id = :item_id";

      return DbConnect::execute($sql, [':item_id' => $itemId]);

    } catch(PDOException $e) {
      ErrorHandler::log('商品画像の削除に失敗 : ' . $e->getMessage());
      throw new Exception('商品画像の削除に失敗しました');
    }
  }


  public function getMainImageByItemId(int $itemId): array|false
  {
    try {
      $sql = "SELECT filename FROM item_images
              WHERE item_id = :item_id
              ORDER BY sort_order
              LIMIT 1";
      
      return DbConnect::fetch($sql, [':item_id' => $itemId]);

    } catch(PDOException $e) {
      ErrorHandler::log('メイン画像の取得に失敗 : ' . $e->getMessage());
      throw new Exception('メイン画像の取得に失敗しました');
    }
  }

  public function getImagesByItemId(int $itemId): array|false
  {
    try {
      $sql = "SELECT * FROM item_images WHERE item_id = :item_id ORDER BY sort_order";
      return DbConnect::fetchAll($sql, [':item_id' => $itemId]);

    } catch(PDOException $e) {
      ErrorHandler::log('商品情報と全画像の取得に失敗 : ' . $e->getMessage());
      throw new Exception('商品情報と全画像の取得に失敗しました');
    }
  }

  public function getItemIdByImageId(int $imageId): ?int
  {
    try {
      $sql = "SELECT item_id FROM item_images WHERE id = :id";
      return DbConnect::fetchColumn($sql, [':id' => $imageId]);

    } catch(PDOException $e) {
      ErrorHandler::log('商品情報と全画像の取得に失敗 : ' . $e->getMessage());
      throw new Exception('商品情報と全画像の取得に失敗しました');
    }
  }


}