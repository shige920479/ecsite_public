<?php
namespace App\Middleware;

use App\Exceptions\ErrorHandler;
use App\Repositories\ItemRepository;
use App\Services\Core\SessionService;
use App\Utils\RequestHelper;

class AuthMiddleware
{
  public const ROLE_ADMIN = 'admin';
  public const ROLE_OWNER = 'owner';
  public const ROLE_USER = 'user';

  public static function checkAuth(string $authenticator): void
  {
    switch ($authenticator) {

      case self::ROLE_ADMIN :
        if(empty($_SESSION['admin'])) {
          if(RequestHelper::isAjax()) {
            http_response_code(401);
            echo json_encode([
              'success' => false,
              'message' => 'ログインが必要です'
            ]);
            exit;
          }
          redirect('/admin/login');
        }
        break;

      case self::ROLE_OWNER :
        if(empty($_SESSION['owner'])) {
          if(RequestHelper::isAjax()) {
            http_response_code(401);
            echo json_encode([
              'success' => false,
              'message' => 'ログインが必要です'
            ]);
            exit;
          }
          redirect('/owner/login');
        }
        break;

      case self::ROLE_USER :
        if(empty($_SESSION['user'])) {
          if(RequestHelper::isAjax()) {
            http_response_code(401);
            echo json_encode([
              'success' => false,
              'message' => 'ログインが必要です'
            ]);
            exit;
          }
          redirect('/login');
        }
        break;

      default :
        ErrorHandler::log("不正なアクセス : {$authenticator}");
        ErrorHandler::redirectWithCode(400);
        break;
    }
  }

  public static function redirectIfAuthenticated(string $authenticator): void
  {
    switch ($authenticator) {
      case self::ROLE_ADMIN :
        if(! empty($_SESSION['admin'])) redirect('/admin/home');
        break;
      case self::ROLE_OWNER :
        if(! empty($_SESSION['owner'])) redirect('/owner/home');
          break;
      case self::ROLE_USER :
        if(! empty($_SESSION['user'])) redirect('');
        break;
      default :
        ErrorHandler::log('不正なアクセス');
        ErrorHandler::redirectWithCode(400);
        break;
    }
  }

  public static function checkOwnerShip(string $authenticator, int $targetId)
  {
    if($_SESSION[$authenticator]['id'] !== $targetId) {
      ErrorHandler::log(
        "不正なアクセス: session_id=>{$_SESSION[$authenticator]['id']} / target_id=>{$targetId}");
      ErrorHandler::redirectWithCode(400);
    }
  }

  public static function authorizeItemOwner(int $itemId): void
  {
    if ($itemId <= 0) {
        ErrorHandler::log("無効な item_id: {$itemId}");
        ErrorHandler::redirectWithCode(403);
    }

    $itemRepo = new ItemRepository();
    $ownerId = $itemRepo->getOwnerIdByItemId($itemId);
    if(SessionService::get('owner.id') !== $ownerId) {
      ErrorHandler::log("不正な商品アクセス: item_id={$itemId}");
      ErrorHandler::redirectWithCode(403);
    }
  }
}