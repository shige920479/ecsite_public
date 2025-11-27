<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

use function PHPUnit\Framework\throwException;

class OrderRepository
{
  public function insertOrder(int $userId, int $totalPrice): int
  {
    $sql = "INSERT INTO orders (user_id, total_price) VALUES (:user_id, :total_price)";
    $param = [
      'user_id' => $userId,
      'total_price' => $totalPrice
    ];

    DbConnect::execute($sql, $param);
    return DbConnect::lastInsertID();
  }

  public function registerSessionId(int $orderId, string $sessionId): bool
  {
    try {
      $sql = "UPDATE orders SET stripe_session_id = :stripe_session_id WHERE id = :order_id";
      $success = DbConnect::execute($sql, [
        'stripe_session_id' => $sessionId,
        'order_id' => $orderId
      ]);
      
      if(! $success) {
        throw new Exception('stripe-session-idの登録に失敗(実行結果:false)');
      }

      return true;

    } catch(PDOException $e) {
      ErrorHandler::log('stripe-session-idの登録に失敗 : ' . $e->getMessage());
      throw new Exception('stripe-session-idの登録に失敗しました');
    }
  }

  public function findById(int $orderId): array|bool
  {
    try {
      $sql = "SELECT id, user_id, stripe_session_id, status FROM orders WHERE id = :order_id";

      return DbConnect::fetch($sql, ['order_id' => $orderId]);

    } catch (PDOException $e) {
      ErrorHandler::log('オーダー情報の取得に失敗 : ' . $e->getMessage());
      throw new Exception('オーダー情報の取得に失敗しました');
    }
  }

  /**
   * オーダー削除（キャンセル時の処理）
   */
  public function deleteById(int $orderId): bool
  {
    try {
      $sql = "DELETE FROM orders WHERE id = :order_id";

      return DbConnect::execute($sql, ['order_id' => $orderId]);

    } catch (PDOException $e) {
      ErrorHandler::log('オーダー情報の削除に失敗 : ' . $e->getMessage());
      throw new Exception;
    }
  }

}