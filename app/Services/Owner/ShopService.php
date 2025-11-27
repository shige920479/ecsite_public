<?php
namespace App\Services\Owner;

use App\Exceptions\ErrorHandler;
use App\Repositories\ShopRepository;
use App\Services\Core\SessionService;
use App\Services\Helper\ImageHandler;
use App\Services\Helper\ImageUploadHelper;
use Exception;

class ShopService
{
  private ShopRepository $repository;
  private ImageHandler $imageHandler;
  
  public function __construct()
  {
    $this->repository = new ShopRepository();
    $this->imageHandler = new ImageHandler();
  }

  public function getShop(int $id)
  {
    try {
      return $this->repository->getByOwnerId($id);
    } catch (Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }
  public function getShopById(int $id)
  {
    try {
      return $this->repository->getById($id);
    } catch (Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function register(array $request)
  {
    $tempPath = SessionService::get('tmp_image_path') ?? null;

    $hasNewUpload = isset($request['image']) && is_array($request['image']) &&
     isset($request['image']['error']) && $request['image']['error'] === UPLOAD_ERR_OK;

    if($hasNewUpload) {
      $filename =  $this->imageHandler->saveImage($request['image']);
      if($filename === null) {
        SessionService::set('errors.image', '画像が正しくアップロードされていません');
        return null;
      } 
      // 画像の差し替えが生じた場合の処理(tmpフォルダの画像を削除)
      if($tempPath !== null && str_starts_with($tempPath, '/tmp/')) {
        $this->imageHandler->deleteTempImage($tempPath);
      }

    } elseif($tempPath !== null && str_starts_with($tempPath, '/tmp/')) {
      $filename = $this->imageHandler->promoteTmpToFinal($tempPath);

      if($filename === null) {
        SessionService::set('errors.image', '画像が正しくアップロードされていません');
        return null;
      } else {
        $this->imageHandler->deleteTempImage($tempPath);
      }
    } else {
      SessionService::set('errors.image', '画像が選択されていません');
      return null;
    }

    $request['owner_id'] = SessionService::get('owner.id');
    $request['filename'] = $filename;

    try {
      return $this->repository->insert($request);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }
}