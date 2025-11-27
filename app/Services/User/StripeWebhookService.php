<?php
namespace App\Services\User;

use App\Exceptions\ErrorHandler;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;


class StripeWebhookService
{
  public function __construct(
    private OrderRepository $orderRepo,
    private CartRepository $cartRepo
  ){}

  public function registerStripeSessionId(int $orderId, string $sessionId): void
  {
    $order = $this->orderRepo->findById($orderId);
    if(! empty($order['stripe_session_id'])) return;
    
    $this->orderRepo->registerSessionId($orderId, $sessionId);
  }
    
  public function clearOrderedItemsFromCart(int $orderId): void
  {
    $order = $this->orderRepo->findById($orderId);
    if(! $order) {
      ErrorHandler::log("不正なWebhookアクセス: 存在しないorder_id={$orderId}");
      //実務では通知処理も
      http_response_code(200); // 永続的なエラーなので200で戻す
      return;
    }
    $userId = $order['user_id'];
    $this->cartRepo->deleteByUserIdAndOrderId($userId, $orderId);
  }
}