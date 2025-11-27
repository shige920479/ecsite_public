<?php
namespace App\Services\User;

use App\Controller\Owner\StockController;
use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Repositories\CartRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Core\SessionService;
use App\Services\Helper\StripeService;
use App\Services\Helper\StripeSessionClient;
use Exception;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class CheckoutService
{
  public function __construct(
      private CartRepository $cartRepo,
      private OrderRepository $orderRepo,
      private OrderItemRepository $orderItemRepo,
      private StockRepository $stockRepo,
      private StripeSessionClient $stripeClient
  ){}

  /**
   * 注文確認画面
   */
  public function getOrderItemsAddCalcTotal(int $userId, array $cartIds): array
  {
    try {
      $tmpOrderItems = $this->cartRepo->getCartItemsByCartIds($userId, $cartIds);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
    
    if(count($tmpOrderItems) < 1) {
      ErrorHandler::log("不正なユーザーid:{$userId}か不正なカートidの入力");
      ErrorHandler::redirectWithCode(403);
    }

    foreach($tmpOrderItems as &$item){
      $priceWithTax = priceWithTax((int)$item['price']);
      $amount = $item['quantity'] * $priceWithTax;
      $item['amount'] = $amount;
    }
    unset($item);

    return $tmpOrderItems;
  }

  public function validateBeforeOrder(int $userId, ?array $cartIds)
  {
    if(empty($cartIds)) {
      SessionService::set('errors.orders', '商品が選択されておりません');
      return false;
    }

    $this->validateCartOwnership($userId, $cartIds);

    $isShortStockId = $this->getShortStockItemId($cartIds);
    $isStopSelling = $this->isStopSelling($cartIds);

    if($isShortStockId !== null || $isStopSelling !== null) {
      if($isShortStockId !== null) {
        SessionService::set("errors.item_{$isShortStockId}", '在庫が不足してます');
      }
      if($isStopSelling !== null) {
        SessionService::set("errors.item_{$isStopSelling}", '販売停止しています');
      }
      return false;
    }
    return true;
  }

  private function validateCartOwnership(int $userId, array $cartIds)
  {
    try {
      $rows = $this->cartRepo->getUserIdByCartIds($cartIds);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
    $userIds = array_column($rows, 'user_id');
    if(count($userIds) !== count($cartIds) || count(array_unique($userIds)) !== 1 || $userIds[0] !== $userId) {
      ErrorHandler::log("購入確定時に不正な入力がありました。user_id :{$userId}");
      ErrorHandler::redirectWithCode(403);
    }
    return true;
  }

  // 在庫や商品状態の最終チェック
  private function getShortStockItemId(array $cartIds)
  {
    try {
      $cartItems = $this->cartRepo->getStockByCartIds($cartIds);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }

    foreach($cartItems as $item) {
      if((int)$item['stock'] < (int)$item['quantity']) {
        return $item['item_id'];
      }
    }
    return null;
  }

  private function isStopSelling(array $cartIds)
  {
    try {
      $cartItems = $this->cartRepo->checkIsSelling($cartIds);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
    foreach($cartItems as $item) {
      if(! $item['is_selling']) {
        return $item['item_id'];
      }
    }
    return null;
  }


  public function createOrder(int $userId, array $cartIds): ?int
  {
    $totalPrice = $this->calcTotalPrice($cartIds);
    try {
      DbConnect::beginTransaction();
      
      // ordersテーブルへの登録
      $orderId = $this->orderRepo->insertOrder($userId, $totalPrice);

      $cartItems = $this->cartRepo->getCartItemsByCartIds($userId, $cartIds);
      foreach($cartItems as &$item) {
        $item['order_id'] = $orderId;
        $item['price'] = priceWithTax((int)$item['price']);
        $item['subtotal'] = $item['price'] * $item['quantity'];
      }
      unset($item);

      // order_itemsテーブルへの登録
      $this->orderItemRepo->insert($cartItems);
      // 在庫テーブルへの登録
      $this->stockRepo->multiInsert($cartItems, REDUCE_STOCK);
      
      DbConnect::commitTransaction();
    
      return $orderId;

    } catch(Exception $e) {
      DbConnect::rollbackTransaction();
      ErrorHandler::log('注文処理中に例外が発生: ' . $e->getMessage());
      return null;
    }
  }

  public function createStripeSession(int $orderId): ?string
  {
    $orderItems = $this->orderItemRepo->getOrderItemsByOrderId($orderId);

    $lineItems = [];
    if(APP_ENV === 'production') {
      $baseUrl = 'https://portfolio-sh0212.com/';
    } else {
      $baseUrl = 'http://localhost:8080/';
    }

    foreach($orderItems as $item) {
      $lineItems[] = [
        'price_data' => [
          'currency' => 'jpy',
          'product_data' => [
            'name' => $item['item_name'],
          ],
          'unit_amount' => (int)$item['price'],
        ],
        'quantity' => $item['quantity'],
      ];
    }


    try {
      $chekoutSessionUrl = $this->stripeClient->createCheckoutSession(
        $lineItems, $baseUrl, $orderId
      );
      return $chekoutSessionUrl;

    } catch(ApiErrorException $e) {
      ErrorHandler::log('Stripe セッション作成エラー : ' . $e->getMessage());
      return null;
    }
  }

  public function handleStripeWebhook(array $payload): void
  {
    // Webhookからの通知を処理して注文ステータス更新
  }

  public function getOrderId(): ?string
  {
    try {
      $sessionId = $_GET['session_id'] ?? null;

      if(! $sessionId) {
        return null;
      }
      
      $session = $this->stripeClient->retrieve($sessionId);

      return $session->metadata->order_id ?? null;

    } catch(Exception $e) {
      ErrorHandler::log("Stripeセッション取得エラー: " . $e->getMessage());
      return null;
    }
  }

  public function deleteOrderAndRestoreStock(int $orderId): void
  {
    try {
      DbConnect::beginTransaction();

      $items = $this->orderItemRepo->getOrderItemsByOrderId($orderId);

      $this->orderItemRepo->deleteByOrderId($orderId);
      $this->orderRepo->deleteById($orderId);
      $this->stockRepo->multiInsert($items, ADD_STOCK);
      
      DbConnect::commitTransaction();

    } catch(Exception) {
      DbConnect::rollbackTransaction();
      throw new Exception('オーダー情報の削除に失敗しました。恐れ入りますが問い合わせ窓口へご連絡願います');
    }
  }

  // 後でprivateへ戻す
  protected function calcTotalPrice(array $cartIds)
  {
    try {
      $cartItems = $this->cartRepo->getPriceAndQuantity($cartIds);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
    $totalPrice = 0;
    if(count($cartItems) > 0) {
      foreach($cartItems as $item) {
        $totalPrice += priceWithTax((int)$item['price']) * $item['quantity'];
      }
    }
    return $totalPrice;


  }

}