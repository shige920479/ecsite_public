<?php
namespace App\Controller\Owner;

use App\Controller\BaseController;
use App\Middleware\AuthMiddleware;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\ItemImageRepository;
use App\Repositories\ItemRepository;
use App\Services\Core\SessionService;
use App\Services\Owner\ImageService;
use App\Services\Validation\ImageValidation;

class ImageController extends BaseController
{
  private ImageService $imageService;
  private ImageValidation $validator;
  private ItemImageRepository $imageRepo;
  private ItemRepository $itemRepo;
  private ItemCategoryRepository $itemCategoryRepo;

  public function __construct()
  {
    parent::__construct();
    $this->imageService = new ImageService();
    $this->validator = new Imagevalidation();
    $this->imageRepo = new ItemImageRepository();
    $this->itemRepo = new ItemRepository();
    $this->itemCategoryRepo = new ItemCategoryRepository();
    AuthMiddleware::checkAuth('owner');
  }

  public function create(string $itemId)
  {
    AuthMiddleware::authorizeItemOwner($itemId);
    $item = $this->itemRepo->findItemById((int)$itemId);
    $categoryName = $this->itemCategoryRepo->getNameById($item->item_category_id);

    $this->render(APP_PATH . '/Views/owner/images/create.php', [
      'item' => $item,
      'categoryName' => $categoryName,
      'is_confirm' => true
    ]);
  }

  public function store(string $itemId)
  {
    AuthMiddleware::authorizeItemOwner($itemId);
    // upload or tmp に画像があるか確認
    if(! $this->validator->hasAtLeastOneUploadedImage($this->request)) {
      SessionService::set('errors', $this->validator->getErrors());
      redirect("/owner/item/{$itemId}/image");
    }
    // uploadがあればバリデーション＆一時保存
    $hasUpload = $this->validator->hasUploadImage($this->request);
    if($hasUpload) {
      $validatedFiles = $this->imageService->validateAndMoveTmp($this->request);
      if($validatedFiles === null) {
        redirect("/owner/item/{$itemId}/image");
      }
    }
    // 登録処理
    $storePaths = SessionService::get('tmp_image_path');
    ksort($storePaths); // エラー再入力・画像変更等で配列順が変わるのでsort順に並べ替え

    if(! $this->imageService->storeImage($storePaths, $itemId)) {
      redirect("/owner/item/{$itemId}/image");
    }
    SessionService::set('success', '商品画像を登録しました');
    redirect("/owner/item/{$itemId}/stock");
  }

  public function edit(int $itemId)
  {
    AuthMiddleware::authorizeItemOwner($itemId);
    $item = $this->itemRepo->findItemById($itemId);
    $categoryName = $this->itemCategoryRepo->getNameById($item->item_category_id);
    $images = $this->imageRepo->getImagesByItemId($itemId);

    $this->render(APP_PATH . '/Views/owner/images/edit.php', [
      'item' => $item,
      'categoryName' => $categoryName,
      'images' => $images,
      'is_confirm' => true
    ]);
  }

  public function update(int $itemId)
  {
    AuthMiddleware::authorizeItemOwner($itemId);

    // 更新データがあるか確認
    if(! $this->imageService->hasAtLeastOneUpdated($this->request)) {
      SessionService::set('errors.image', '更新情報がありません、再度入力願います');
      redirect("/owner/item/{$itemId}/image/edit");
    }

    // アップロード画像があるかチェックし、あればバリデーション実行
    $hasUpload = $this->validator->hasUploadImage($this->request);
    if($hasUpload) {
      $validatedFiles = $this->imageService->validateAndMoveTmp($this->request);
      if($validatedFiles === null) {
        redirect("/owner/item/{$itemId}/image/edit");
      }
    }

    if(! $this->imageService->updateImage($itemId, $this->request)) {
      redirect("/owner/item/{$itemId}/image/edit");
    }
    SessionService::set('success', '商品画像を更新しました');
    redirect("/owner/items");
  }


  public function delete()
  {    
    if(session_status() !== PHP_SESSION_ACTIVE) session_start();

    $data = json_decode(file_get_contents('php://input'), true);
    $filePath = $data['filename'] ?? '';
    $filename = basename($data['filename'] ?? '');
    $imageId = $data['image_id'] ?? '';

    header('Content-Type: application/json; charset=utf-8');

    if (empty($filename || empty($imageId))) {
      echo json_encode([
        'success' => false, 
        'message' => 'パラメーターが不足しています'
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }
    
    $itemId = $this->imageRepo->getItemIdByImageId($imageId);
    AuthMiddleware::authorizeItemOwner($itemId);

    // DBの商品画像レコードを削除
    if(! $this->imageService->deleteImage($imageId)) {
      echo json_encode([
        'success' => false,
        'message' => 'DBからの削除に失敗しました'
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    // セキュリティ上のパス検証
    $imagePath = realpath(PUBLIC_PATH . $filePath); 
    if(! $imagePath || ! str_starts_with($imagePath, UPLOAD_PATH)) {
      echo json_encode([
        'success' => false,
        'message' => 'パスが不正です'
      ], JSON_UNESCAPED_UNICODE);
      exit;
    }

    // uploads/item-images内の画像を削除
    if(file_exists($imagePath)) {
      if(! unlink($imagePath)) {
        echo json_encode([
          'success' => false,
          'message' => '画像ファイルの削除に失敗しました'
        ], JSON_UNESCAPED_UNICODE);
        exit;
      }
    }

    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    exit;
  }
}