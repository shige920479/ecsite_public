<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDO;
use PDOException;

class CartRepository
{

  public function existByItemId(int $userId, $itemId): bool
  {
    try {
      $sql = "SELECT COUNT(id) FROM carts WHERE user_id = :user_id AND item_id = :item_id";

      return DbConnect::fetchColumn($sql, ['user_id' => $userId, 'item_id' => $itemId]) > 0;

    } catch(PDOException $e) {
      ErrorHandler::log('カート内商品情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('カート内商品情報の取得に失敗しました');
    }
  }

  public function insert(int $userId, $itemId, $quantity): bool
  {
    try {
      $sql = "INSERT INTO carts (user_id, item_id, quantity) VALUES (:user_id, :item_id, :quantity)";
      $param = [
        'user_id' => $userId,
        'item_id' => $itemId,
        'quantity' => $quantity
      ];

      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('カート登録に失敗 : ' . $e->getMessage());
      throw new Exception('カート登録に失敗しました');
    }
  }

  public function getItemInCart(int $userId): array
  {
    try {
      $sql = "SELECT ct.id as cart_id, ct.item_id as item_id, it.name as item_name,
              sh.name as shop_name, ct.quantity as quantity, it.price AS price, img.filename as filename,
              it.is_selling as is_selling FROM carts as ct
              LEFT JOIN items as it ON it.id = ct.item_id
              LEFT JOIN shops as sh ON sh.id = it.shop_id
              LEFT JOIN (SELECT item_id, filename FROM item_images WHERE sort_order = 1) AS img
              ON img.item_id = it.id
              WHERE user_id = :user_id
              ORDER BY ct.updated_at DESC";
      
      return DbConnect::fetchAll($sql, ['user_id' => $userId]);

    } catch(PDOException $e) {
      ErrorHandler::log('カート情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('カート情報の取得に失敗しました');
    }
  }

  public function deleteCartItemById(int $cartId): bool
  {
    try {
      $sql = "DELETE FROM carts WHERE id = :id";

      return DbConnect::execute($sql, ['id' => $cartId]);

    } catch(PDOException $e) {
      ErrorHandler::log('カートアイテムの削除に失敗 : ' . $e->getMessage());
      throw new Exception('カートアイテムの削除に失敗しました');
    }
  }

  public function getCartItemsByCartIds(int $userId, array $cartIds): array
  {
    list($placeholder, $param) = $this->buildInsertPlaceholders($cartIds);
    try {
      $sql = "SELECT ct.id as cart_id, ct.item_id as item_id, it.name as item_name,
              ct.quantity as quantity, it.price as price  FROM carts as ct
              LEFT JOIN items as it ON it.id = ct.item_id
              WHERE ct.user_id = :user_id AND ct.id IN {$placeholder}";
      $param['user_id'] = $userId;

      return DbConnect::fetchAll($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('購入商品情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('購入商品情報の取得に失敗しました');
    }
  }

  public function updateQuantity(int $cartId, $quantity): bool
  {
    try {
      $sql = "UPDATE carts SET quantity = :quantity WHERE id = :id";
      $param = [
        'quantity' => $quantity,
        'id' => $cartId
      ];

      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('カート内商品の数量変更に失敗 : ' . $e->getMessage());
      throw new Exception('カート内商品の数量変更に失敗しました');
    }
  }

  public function getCartItemById(int $cartId): array|bool
  {
    try {
      $sql = "SELECT id, user_id, item_id, quantity FROM carts WHERE id = :id";
      return DbConnect::fetch($sql, ['id' => $cartId]);

    } catch(PDOException $e) {
      ErrorHandler::log('カート情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('カート情報の取得に失敗しました');      
    }
  }
  /**
   * カートidからユーザーidを取得（各カートがログインユーザーのものか判定する材料を取得）
   */
  public function getUserIdByCartIds(array $cartIds): array
  {
    list($placeholder, $param) = $this->buildInsertPlaceholders($cartIds);
    try {
      $sql = "SELECT user_id FROM carts WHERE id IN {$placeholder}";
      
      return DbConnect::fetchAll($sql, $param);

    } catch (PDOException $e) {
      ErrorHandler::log('ユーザー情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('ユーザー情報の取得に失敗しました');   
    }
  }

  /**
   * カートidから商品毎の在庫数量を取得
   */
  public function getStockByCartIds(array $cartIds): array
  {
    list($placeholder, $param) = $this->buildInsertPlaceholders($cartIds);

    try {
      $sql = "SELECT ct.item_id AS item_id, st.stock AS stock, ct.quantity AS quantity
              FROM carts AS ct
              LEFT JOIN (SELECT item_id, SUM(stock_diff) AS stock FROM stocks GROUP BY item_id) AS st
              ON st.item_id = ct.item_id
              WHERE ct.id IN {$placeholder}";
      
      return DbConnect::fetchAll($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('在庫情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('在庫情報の取得に失敗しました');     
    }
  }

  /**
   * 商品毎に販売情報（is_selling）を取得
   */
  public function checkIsSelling(array $cartIds): array
  {
    list($placeholder, $param) = $this->buildInsertPlaceholders($cartIds);
    try {
      $sql = "SELECT it.id AS item_id, it.is_selling AS is_selling FROM carts AS ct
              LEFT JOIN items AS it ON it.id = ct.item_id
              WHERE ct.id IN {$placeholder}";
      
      return DbConnect::fetchAll($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('販売情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('販売情報の取得に失敗しました');   
    }
  }

  public function getPriceAndQuantity(array $cartIds)
  {
    list($placeholder, $param) = $this->buildInsertPlaceholders($cartIds);
    try {
      $sql = "SELECT ct.id AS cart_id, ct.item_id AS item_id, it.price AS price, ct.quantity AS quantity
              FROM carts AS ct
              LEFT JOIN items as it ON it.id = ct.item_id
              WHERE ct.id IN {$placeholder}";
      
      return DbConnect::fetchAll($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('価格・数量情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('価格・数量情報の取得に失敗しました');   
    }
  }

  public function deleteByUserIdAndOrderId(int $userId, int $orderId): bool
  {
    try {
      $sql = "DELETE FROM carts WHERE user_id = :user_id
              AND item_id IN (
                SELECT item_id FROM order_items WHERE order_id = :order_id
              )";
      
      return DbConnect::execute($sql, [
        'user_id' => $userId,
        'order_id' => $orderId
      ]);

    } catch(PDOException $e) {
      ErrorHandler::log('カート削除に失敗 : ' . $e->getMessage());
      throw new Exception('カート削除に失敗しました、購入手続きは完了していますのでお手数ですが手動で削除願います');
    }
  }

  private function buildInsertPlaceholders(array $cartIds)
  {
    $placeholders = [];
    $param = [];
    foreach($cartIds as $index => $value){
      $placeholders[] = ":cart_{$index}";
      $param["cart_{$index}"] = $value;
    }
    $placeholder = '(' . implode(',', $placeholders) . ')';

    return [$placeholder, $param];
  }
}