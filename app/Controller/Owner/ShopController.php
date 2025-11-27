<?php
namespace App\Controller\Owner;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Middleware\AuthMiddleware;
use App\Repositories\ShopRepository;
use App\Services\Core\SessionService;
use App\Services\Helper\ImageHandler;
use App\Services\Helper\ShopFormHelper;
use App\Services\Owner\ShopEditService;
use App\Services\Owner\ShopService;
use App\Services\Validation\ShopValidation;
use Exception;

class ShopController extends BaseController
{
  private ShopService $shopService;
  private ShopEditService $shopEditService;
  private ShopFormHelper $formHelper;
  private ImageHandler $imageHandler;
  private ShopRepository $shopRepo;

  public function __construct()
  {
    parent::__construct();
    $this->shopService = new ShopService;
    $this->shopEditService = new ShopEditService();
    $this->imageHandler = new ImageHandler();
    $this->formHelper = new ShopFormHelper(new ShopValidation(), $this->imageHandler);
    $this->shopRepo = new ShopRepository();
    AuthMiddleware::checkAuth('owner');
  }

  public function showHome()
  {
    $shop = $this->shopService->getShop(SessionService::get('owner.id'));
    $this->render(APP_PATH . '/Views/owner/home.php', ['shop' => $shop]);
  }

  public function showForm()
  {
    $this->render(APP_PATH . '/Views/owner/shops/shop.php');
  }

  public function registerShop()
  {
    try {
      $redirect = $this->formHelper->handleValidationAndTempImage($this->request);
      if($redirect !== null) redirect($redirect);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(400);
    }

    $result = $this->shopService->register($this->request);
    if($result === null) {
      redirect('/owner/registerShop');
    }

    if(SessionService::get('tmp_image_path') !== null) {
      SessionService::forget('tmp_image_path');
    }
    SessionService::set('success', 'ショップ情報を登録しました');
    redirect('/owner/home');
  }

  public function editShop(int $id)
  {
    AuthMiddleware::checkOwnerShip('owner', $this->shopRepo->getById($id)->owner_id);
    $shop = $this->shopService->getShopById($id);
    $this->render(APP_PATH . '/Views/owner/shops/edit.php', [
      'shop' => $shop,
      'is_confirm' => true,
    ]);
  }

  public function updateShop(int $id)
  {
    AuthMiddleware::checkOwnerShip('owner', $this->shopRepo->getById($id)->owner_id);
    try {
      $redirect = $this->formHelper->handleValidationAndTempImage($this->request, 'edit', $id);
      if($redirect !== null) redirect($redirect);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(400);
    }

    $updated = $this->shopEditService->update($id, $this->request);

    if(! $updated || $updated === null) {
      redirect("/owner/shop/{$id}/edit");
    }
    if(SessionService::get('tmp_image_path') !== null) {
      SessionService::forget('tmp_image_path');
    }
    if($updated->filename !== $this->request['current_filename']) {
      $this->imageHandler->deleteUploadedImage($this->request['current_filename']);
    }
    SessionService::set('success', 'ショップ情報を更新しました');
    redirect('/owner/home');
  }

  public function deleteTemp(): void
  {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    $data = json_decode(file_get_contents('php://input'), true);
    $filename = basename($data['filename'] ?? '');
    $path = TMP_PATH . '/' . $filename;

    header('Content-Type: application/json; charset=utf-8');

    if (! $filename) {
      echo json_encode([
        'success' => false,
        'message' => 'ファイル名が指定されていません'
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    if (file_exists($path)) {
      if (!unlink($path)) {
        echo json_encode([
          'success' => false,
          'message' => 'ファイル削除に失敗しました'
        ], JSON_UNESCAPED_UNICODE);
        exit;
      }

      if (!empty($_SESSION['tmp_image_path']) && basename($_SESSION['tmp_image_path']) === $filename) {
        unset($_SESSION['tmp_image_path']);
      }
      
      echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
      exit;

    } else {
      echo json_encode([
        'success' => false,
        'message' => '画像ファイルが存在しません'
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
  }
}