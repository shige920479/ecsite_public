<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Middleware\AuthMiddleware;
use App\Repositories\CartRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Core\SessionService;
use App\Services\Helper\StripeSessionClient;
use App\Services\User\CheckoutService;
use Exception;

class CheckoutController extends BaseController
{
  private CheckoutService $checkoutService;

  public function __construct()
  {
    parent::__construct();
    $this->checkoutService = new CheckoutService(
      new CartRepository(),
      new OrderRepository(),
      new OrderItemRepository(),
      new StockRepository(),
      new StripeSessionClient()
    );
    AuthMiddleware::checkAuth('user');
  }

  /**
   * カートidを受け取り注文確認用データを取得～表示
   */
  public function showOrder(): void
  {
    if(! isset($_SESSION['errors']) && empty($this->request['cart_id'])) {
      SessionService::set('order_error', 'カートが空です、商品を選択してください');
      redirect('/cart');
    }
    $userId = $_SESSION['user']['id'];
    $tmpOrderItems = $this->checkoutService->getOrderItemsAddCalcTotal($userId, $this->request['cart_id']);
    $total = array_sum(array_column($tmpOrderItems, 'amount'));
    $redirectPath = str_replace(PATH, '', $_SERVER['REQUEST_URI']);

    $this->render(APP_PATH . '/Views/user/order-confirm.php', [
      'tmpOrderItems' => $tmpOrderItems,
      'total' => $total,
      'redirectPath' => $redirectPath
    ]);
  }

  /**
   * 注文データの保存 セッション・DBへ
   */
  public function confirm()
  {
    $userId = $_SESSION['user']['id'];
    if(! $this->checkoutService->validateBeforeOrder($userId, $this->request['cart_ids'] ?? null)) {
      redirect(urldecode($this->request['redirect_path']));
    }

    $orderId = $this->checkoutService->createOrder($userId, $this->request['cart_ids']);
    if($orderId === null) {
      SessionService::set('errors.order', '注文処理に失敗しました。恐れ入りますが、再度カート内容をご確認ください');
      redirect('/cart');
    }
    SessionService::set('order_id', $orderId);
    
    header('Content-Type: application/json');
    $url = $this->checkoutService->createStripeSession($orderId);
    if($url === null) {
      SessionService::set('errors.payment', '決済処理に失敗しました、時間をおいて再度お試し願います');
      redirect('/cart');
    }
    header("Location:{$url}");
    exit;
  }

  public function success()
  {
    $orderId = $this->checkoutService->getOrderId();
    $this->render(APP_PATH . '/Views/user/checkout-success.php', ['orderId' => $orderId]);
  }

  public function cancel()
  {
    $orderId = $this->checkoutService->getOrderId();
    try {
      $this->checkoutService->deleteOrderAndRestoreStock($orderId);
    } catch(Exception $e) {
      SessionService::set('errors.cancel', $e->getMessage());
    }
    
    $this->render(APP_PATH . '/Views/user/checkout-cancel.php');
  }

}