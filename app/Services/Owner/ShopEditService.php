<?php
namespace App\Services\Owner;

use App\Exceptions\ErrorHandler;
use App\Repositories\ShopRepository;
use App\Services\Core\SessionService;
use App\Services\Helper\ImageHandler;
use Exception;

class ShopEditService
{
  private ShopRepository $repository;

  public function __construct()
  {
    $this->repository = new ShopRepository();
  }

  public function update(int $id, array $request)
  {
    $imageHandler = new ImageHandler();
    $tempPath = SessionService::get('tmp_image_path') ?? null;
    $hasNewUpload = isset($request['image']) && is_array($request['image']) &&
      isset($request['image']['error']) && $request['image']['error'] === UPLOAD_ERR_OK;
    
    // 画像の更新がある場合
    if($hasNewUpload) {
      $filename = $imageHandler->saveImage($request['image']);
      if($filename === null) {
        SessionService::set('errors.image', '画像が正しくアップロードされていません');
        return null;
      }
      
      if($tempPath !== null && str_starts_with($tempPath, '/tmp/')) {
        $imageHandler->deleteTempImage($tempPath);
      }
    
    } elseif($tempPath !== null && str_starts_with($tempPath, '/tmp/')) {
      $filename = $imageHandler->promoteTmpToFinal($tempPath);
      if($filename === null) {
        SessionService::set('errors.image', '画像が正しくアップロードされていません');
        return null;
      } else {
        $imageHandler->deleteTempImage($tempPath);
      }
    } else {
      $filename = $request['current_filename'];
    }
    $request['filename'] = $filename;

    try {
      return $this->repository->update($id, $request);

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }


  }





}