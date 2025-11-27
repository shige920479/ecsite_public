<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Middleware\AuthMiddleware;
use App\Repositories\CartRepository;
use App\Repositories\FavoriteRepository;
use App\Services\Core\SessionService;
use App\Services\User\FavoriteService;
use Exception;

class FavoriteController extends BaseController
{
  private FavoriteService $favoriteService;
  private FavoriteRepository $favoriteRepo;
  private CartRepository $cartRepo;

  public function __construct()
  {
    parent::__construct();
    $this->favoriteRepo = new FavoriteRepository();
    $this->cartRepo = new CartRepository();
    $this->favoriteService = new FavoriteService($this->favoriteRepo, $this->cartRepo);
    AuthMiddleware::checkAuth('user');
  }


  public function showFavorite(): void
  {
    $userId = $_SESSION['user']['id'];
    $favorites = $this->favoriteRepo->getAllByUserId($userId);

    $this->render(APP_PATH . '/Views/user/favorite.php', ['favorites' => $favorites]);
  }

  public function toggle(): void
  {
    $body = json_decode(file_get_contents('php://input'), true);
    $itemId = (int)$body['item_id'] ?? 0;
    $userId = $_SESSION['user']['id'];

    header('Content-Type: application/json; charset=utf-8');

    if(! $itemId) {
      ErrorHandler::log('商品IDの情報がありません、不正な入力です');
      echo json_encode([
        'success' => false,
        'message' => '商品IDが不正です'
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    try {
      if($this->favoriteRepo->isFavorited($userId, $itemId)) {
        $this->favoriteRepo->remove($userId, $itemId);
        echo json_encode([
          'success' => true,
          'isFavorite' => false
        ], JSON_UNESCAPED_UNICODE);
        exit;
      } else {
        $this->favoriteRepo->add($userId, $itemId);
        echo json_encode([
          'success' => true,
          'isFavorite' => true
        ], JSON_UNESCAPED_UNICODE);
        exit;
      }
    } catch(Exception $e) {
      echo json_encode([
        'success' => false,
        'message' => 'Internal Error'
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }

  public function delete(): void
  {
    $body = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user']['id'];
    $favoriteId = $body['favorite_id'] ?? null;

    header('Content-Type: application/json; charset=utf-8');

    if(! $favoriteId) {
      echo json_encode([
        'success' => false,
        'message' => 'お気に入り登録がありません'
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    try {
      $favorite = $this->favoriteRepo->getById($favoriteId);
      if(! $favorite) {
        echo json_encode([
          'success' => false,
          'message' => 'お気に入り登録がありません'
        ], JSON_UNESCAPED_UNICODE);
        exit;
      }
      if($userId !== $favorite['user_id']) {
        ErrorHandler::log("不正な入力 ログインユーザーID: {$userId} お気に入りID: {$favoriteId}");
        echo json_encode([
          'success' => false,
          'message' => '不正な入力がありました'
        ], JSON_UNESCAPED_UNICODE);
        exit;
      }
      $this->favoriteRepo->deleteById($favoriteId);
      echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
      exit;

    } catch(Exception $e) {
      echo json_encode([
        'success' => false,
        'message' => 'Internal Error ' . $e->getMessage()
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }

  public function moveToCart(): void
  {
    try {
      $favorite = $this->favoriteRepo->getById($this->request['favorite_id']);
      $userId = $_SESSION['user']['id'];
      if($userId !== $favorite['user_id']) {
        ErrorHandler::log("不正な入力 ログインユーザーID: {$userId} お気に入りID: {$this->request['favorite_id']}");
        ErrorHandler::redirectWithCode(403);
      }
      $this->favoriteService->addInCartAndRemoveFavorite($favorite);

      redirect('/cart');

    } catch(Exception $e) {
      SessionService::set('errors.favorite', 'カートへの移動時に問題が発生しました: '. $e->getMessage());
      redirect('/favorite');
    }
  }

  public function moveFromCart()
  {
    $body = json_decode(file_get_contents('php://input'), true);
    $cartId = $body['cart_id'];
    $userId = $_SESSION['user']['id'];

    try {
      $cartItem = $this->cartRepo->getCartItemById($cartId);

      if(! $cartItem) {
        echo json_encode([
          'success' => false,
          'message' => '商品がカートに存在していません'
        ], JSON_UNESCAPED_UNICODE);
        exit;
      }

      if($userId !== $cartItem['user_id']) {
        ErrorHandler::log("不正な入力 ログインユーザーID: {$userId} カートID: {$cartId}");
        echo json_encode([
          'success' => false,
          'message' => '不正なカートIDが入力されました'
        ], JSON_UNESCAPED_UNICODE);
        exit;
      }

      $this->favoriteService->addInFavoriteAndRemoveCart($cartItem);
      echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
      exit;

    } catch(Exception $e) {
      echo json_encode([
        'success' => false,
        'message' => 'Internal Error ' . $e->getMessage()
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }
}