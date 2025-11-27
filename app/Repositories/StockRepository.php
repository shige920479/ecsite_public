<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

class StockRepository
{
  public function insert(int $itemId, array $request): bool
  {
    try {
      $sql = "INSERT INTO stocks (item_id, stock_diff, reason) VALUES (:item_id, :stock_diff, :reason)";

      return DbConnect::execute($sql, [
        ':item_id' => $itemId,
        ':stock_diff' => $request['stock_diff'],
        ':reason' => $request['reason'] ?? null
      ]);

    } catch(PDOException $e) {
      ErrorHandler::log('在庫登録に失敗 : ' . $e->getMessage());
      throw new Exception('在庫登録に失敗しました');
    }
  }

  public function multiInsert(array $cartItems, string $change): bool
  {
    list($placeholder, $param) = $this->buildInsertPlaceholders($cartItems, $change);
    try {
      $sql = "INSERT INTO stocks (item_id, stock_diff) VALUES {$placeholder}";

      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log('在庫データの一括挿入に失敗 : ' . $e->getMessage());
      throw new Exception('在庫データの一括挿入に失敗しました');
    }
  }

  public function deleteByItemId(int $itemId): bool
  {
    try {
      $sql = "DELETE FROM stocks WHERE item_id = :item_id";

      return DbConnect::execute($sql, [':item_id' => $itemId]);

    } catch(PDOException $e) {
      ErrorHandler::log('在庫情報の削除に失敗 : ' . $e->getMessage());
      throw new Exception('在庫情報の削除に失敗しました');
    }

  }

  public function getCurrentStock(int $itemId): array
  {
    try {
      $sql = "SELECT SUM(stock_diff) as current_stock FROM stocks WHERE item_id = :item_id";

      return DbConnect::fetch($sql, [':item_id' => $itemId]);

    } catch(PDOException $e) {
      ErrorHandler::log('在庫数量の取得に失敗 : ' . $e->getMessage());
      throw new Exception('在庫数量の取得に失敗しました');
    }
  }

  public function buildInsertPlaceholders($cartItems, $change)
  {
    $placeholders = [];
    $param = [];
    foreach($cartItems as $index => $value){
      $placeholders[] = "(:item_id_{$index}, :stock_diff_{$index})";
      $param["item_id_{$index}"] = $value['item_id'];
      $param["stock_diff_{$index}"] = match ($change) {
        ADD_STOCK => $value['quantity'],
        REDUCE_STOCK => $value['quantity'] * -1
      };
    }
    $placeholder = implode(',', $placeholders);

    return [$placeholder, $param];
  }

}