<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Middleware\AuthMiddleware;
use App\Repositories\CartClearErrorsRepository;
use App\Repositories\CartRepository;
use App\Repositories\ItemRepository;
use App\Repositories\StockRepository;
use App\Services\Core\SessionService;
use App\Services\User\CartService;
use App\Services\Validation\CartValidation;
use Exception;

class CartController extends BaseController
{
  private CartValidation $validator;
  private ItemRepository $itemRepo;
  private CartRepository $cartRepo;
  private StockRepository $stockRepo;
  private CartService $cartService;
  private CartClearErrorsRepository $cartClearErrorRepo;
  private int $userId;

  public function __construct()
  {
    parent::__construct();
    AuthMiddleware::checkAuth('user');
    $this->validator = new CartValidation();
    $this->itemRepo = new ItemRepository();
    $this->cartRepo = new CartRepository();
    $this->stockRepo = new StockRepository();
    $this->cartService = new CartService($this->cartRepo, $this->itemRepo, $this->stockRepo);
    $this->cartClearErrorRepo = new CartClearErrorsRepository();
    $this->userId = (int)$_SESSION['user']['id'] ?? null;
  }

  public function showCart()
  {
    $cartItems = $this->cartRepo->getItemInCart($this->userId);
    
    // カートクリアエラーの場合の表示
    $cartClearError = $this->cartClearErrorRepo->findByUserId($this->userId);
    if($cartClearError) {
      SessionService::set('errors.cart-clear', $cartClearError['error_type']);
      $this->cartClearErrorRepo->delete($cartClearError['id']);
    }

    $this->render(APP_PATH . '/Views/user/cart.php', ['cartItems' => $cartItems]);
  }

  public function addInCart()
  {
    $itemId = (int)$this->request['item_id'];
    $quantity = (int)$this->request['quantity'];
    
    if(! $this->validator->checkQuantity($quantity)){
      SessionService::set('errors.cart_in', '1以上の整数で入力してください');
      redirect('/items/' . $itemId);
    }

    try {
      $result = $this->cartService->validateBeforeInsert($this->userId, $itemId, $quantity);
      if(! $result) {
        if($this->cartService->getLastStatus() === 404) {
          ErrorHandler::redirectWithCode($this->cartService->getLastStatus());
        } else {
          SessionService::set('errors.cart_in', $this->cartService->getLastMessage());
          redirect('/items/' . $itemId);
        }
      }
  
      $this->cartRepo->insert($this->userId, $itemId, $quantity);
      SessionService::set('success', '商品をカートに追加しました');
      redirect('/cart');

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function removeFromCart(int $itemId)
  {
    try {
      if(! $this->cartRepo->existByItemId($this->userId, $itemId)) {
        ErrorHandler::log("不正なカート削除アクセス user_id={$this->userId}, item_id={$itemId}");
        ErrorHandler::redirectWithCode(404);
      }
      if($this->cartRepo->deleteCartItemById($this->request['cart_id'])) {
        redirect('/cart');
      }
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function updateCart()
  {
    $body = json_decode(file_get_contents('php://input'), true);
    $cartId = (int)($body['cart_id'] ?? 0);
    $quantity = (int)($body['quantity'] ?? 1);

    $result = $this->cartService->updateQuantityWithValidation($cartId, $quantity);

    header('Content-Type: application/json; charset=utf-8');

    if(! $result) {
      http_response_code($this->cartService->getLastStatus());
      echo json_encode([
        'success' => false,
        'message' => $this->cartService->getLastMessage()
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
    echo json_encode([
      'success' => true
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }
}