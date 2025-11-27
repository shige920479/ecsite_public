<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

class OrderItemRepository
{
  public function insert(array $order): bool
  {
    try {
      list($placeholder, $param) = $this->createPlaceHolderAndParam($order);
      $sql = "INSERT INTO order_items
              (order_id, item_id, item_name, quantity, price, subtotal)
              VALUES {$placeholder}";

      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('個別注文情報の一括登録に失敗 : ' . $e->getMessage());
      throw new Exception('個別注文情報の一括登録に失敗しました');
    }
  }

  public function getOrderItemsByOrderId(int $orderId): array
  {
    try {
      $sql = "SELECT item_id, item_name, quantity, price, subtotal FROM order_items 
              WHERE order_id = :order_id";
      
      return DbConnect::fetchAll($sql, ['order_id' => $orderId]);

    } catch(PDOException $e) {
      ErrorHandler::log('個別注文情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('個別注文情報の取得に失敗しました');
    }
  }

  public function deleteByOrderId(int $orderId): bool
  {
    try {
      $sql = "DELETE FROM order_items WHERE order_id = :order_id";

      return DbConnect::execute($sql, ['order_id' => $orderId]);

    } catch(PDOException $e) {
      ErrorHandler::log('オーダーアイテムの削除に失敗 : ' . $e->getMessage());
      throw new Exception;
    }
  }

  private function createPlaceHolderAndParam(array $order)
  {
    $placeholderArray = [];
    $param = [];
    foreach($order as $index => $value) {
      $placeholderArray[] =
         "(:order_id{$index}, :item_id{$index}, :item_name{$index}, :quantity{$index}, :price{$index}, :subtotal{$index})";
      $param["order_id{$index}"] = $value['order_id'];
      $param["item_id{$index}"] = $value['item_id'];
      $param["item_name{$index}"] = $value['item_name'];
      $param["quantity{$index}"] = $value['quantity'];
      $param["price{$index}"] = $value['price'];
      $param["subtotal{$index}"] = $value['subtotal'];
    }
    $placeholder = implode(',', $placeholderArray);
    return [$placeholder, $param];
  }

}