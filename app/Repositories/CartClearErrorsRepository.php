<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

class CartClearErrorsRepository
{
  public function insert(int $userId, $orderId, $errorType): bool
  {
    try {
      $sql = "INSERT INTO cart_clear_errors
              (user_id, order_id, error_type)
              VALUES
              (:user_id, :order_id, :error_type)";
      $param = [
        'user_id' => $userId,
        'order_id' => $orderId,
        'error_type' => $errorType
      ];

      return DbConnect::execute($sql, $param);

    } catch(PDOException $e) {
      ErrorHandler::log(
        "カート削除エラーの登録に失敗 ユーザー：{$userId}, オーダー:{$orderId}, エラータイプ:{$errorType} : " . $e->getMessage()
      );
      throw new Exception('カート削除エラーの登録に失敗しました');
    }
  }

  public function delete(int $id): bool
  {
    try {
      $sql = "DELETE FROM cart_clear_errors WHERE id = :id";
      
      return DbConnect::execute($sql, ['id' => $id]);

    } catch(PDOException $e) {
      ErrorHandler::log('カート削除エラー情報の削除に失敗 : ' . $e->getMessage());
      throw new Exception('カート削除エラー情報の削除に失敗しました');
    }
  }

  public function findByUserId(int $userId): array|bool
  {
    try {
      $sql = "SELECT id, order_id, error_type FROM cart_clear_errors WHERE user_id = :user_id";

      return DbConnect::fetch($sql, [':user_id' => $userId]);

    } catch(PDOException $e) {
      ErrorHandler::log('カート削除エラーの情報取得に失敗 : ' . $e->getMessage());
      throw new Exception('カート削除エラーの情報取得に失敗しました');
    }


  }
}