<?php
namespace App\Services\Helper;

use App\Exceptions\ErrorHandler;
use Exception;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageHandler
{
  public function saveTempImage(array $file): ?string
  {
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('tmp_', true) . '.' . $ext;
    $tempDir = APP_PATH . '/../public/tmp/';
    $savePath = $tempDir . $filename;

    if(! move_uploaded_file($file['tmp_name'], $savePath)) {
      return null;
    }

    return '/tmp/' . $filename;
  }

  public function promoteTmpToFinal(string $tempPath, string $saveDir = 'shops')
  {
    $absolutePath = PUBLIC_PATH . $tempPath;
    
    if(! file_exists($absolutePath)) {
      return null;
    }
    
    $ext = pathinfo($absolutePath, PATHINFO_EXTENSION);  // $temPathでもOK
    $filename = uniqid(rand() . '') . '.' . $ext;
    $savePath = PUBLIC_PATH . "/uploads/{$saveDir}/{$filename}";

    try {
      $imageManager = new ImageManager(new Driver());
      $image = $imageManager->read($absolutePath);
      $image->scale(1200, 900);
      $image->toJpeg()->save($savePath);
    } catch(Exception $e) {
      ErrorHandler::log('画像のリサイズに失敗: ' . $e->getMessage());
      return null;
    }

    return $filename;
  }

  public function deleteTempImage(string $relativePath, bool $logIfNotFound = true): bool
  {
    $absolutePath = APP_PATH . '/../public' . $relativePath;
    if(file_exists($absolutePath)) {
      $result = unlink($absolutePath);
      if(! $result) {
        ErrorHandler::log("一時ファイルの削除に失敗: {$absolutePath}");
      }
      return $result;
    }
    if($logIfNotFound) {
      ErrorHandler::log("削除対象のファイルが存在しません: {$absolutePath}");
    }
    return false;
  }

  public function deleteUploadedImage(string $filename, string $saveDir = 'shops', bool $logIfNotFound = true) :bool
  {
    $saveName = basename($filename);
    $absolutePath = APP_PATH. "/../public/uploads/{$saveDir}/" . $saveName;
    if(file_exists($absolutePath)) {
      $result = unlink($absolutePath);
      if(! $result) {
        ErrorHandler::log("アップロード済みファイルの削除に失敗: {$absolutePath}");
      }
      return $result;
    }
    if($logIfNotFound) {
      ErrorHandler::log("削除対象のファイルが存在しません: {$absolutePath}");
    }
    return false;
  }

  public function saveImage(array $file, string $saveDir = 'shops'): ?string
  {
    if($file['error'] !== UPLOAD_ERR_OK) {
      return null;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid(rand() . '') . '.' . $ext;
    $savePath = PUBLIC_PATH . "/uploads/{$saveDir}/{$filename}";

    try {
      $imageManager = new ImageManager(new Driver());
      $image = $imageManager->read($file['tmp_name']);
      $image->scale(1200, 900);
      $image->toJpeg()->save($savePath);

    } catch (Exception $e) {
      ErrorHandler::log('画像のリサイズに失敗: ' . $e->getMessage());
      return null;
    }

    return $filename;
  }

}