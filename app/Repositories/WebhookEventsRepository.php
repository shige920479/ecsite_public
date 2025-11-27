<?php
namespace App\Repositories;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use Exception;
use PDOException;

class WebhookEventsRepository
{
  public function isProcessed(string $eventId): bool
  {
    try {
      $sql = "SELECT COUNT(id) FROM webhook_events WHERE event_id = :event_id";

      return DbConnect::fetchColumn($sql, ['event_id' => $eventId]) > 0;

    } catch(PDOException $e) {
      ErrorHandler::log("webhookイベントID:{$eventId}の存在確認に失敗 : " . $e->getMessage());
      throw new Exception();
    }
  }

  public function markAsProcessed(string $eventId): bool
  {
    try {
      $sql = "INSERT INTO webhook_events (event_id) VALUES (:event_id)";

      return DbConnect::execute($sql, ['event_id' => $eventId]);

    } catch(PDOException $e) {
      ErrorHandler::log("webhookイベントID:{$eventId}の登録に失敗 : " . $e->getMessage());
      throw new Exception();
    }

  }

}