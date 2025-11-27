<?php
namespace App\Services\Validation;

use App\Exceptions\ErrorHandler;
use App\Services\Core\SessionService;

class ShopValidation extends BaseValidation
{
  public function validate(array $request, string $mode = 'create')
  {
    if($this->required('name', $request['name'] ?? null)) {
      $this->maxLength('name', $request['name'], 50);
    }

    $this->required('information', $request['information'] ?? null);
    
    if(! isset($request['is_selling'])) {
      $this->errors['is_selling'] = 'ステータスが選択されておりません';
    } else {
      $this->validBoolean('is_selling', $request['is_selling']);
    }
    
    // $_FIlESに画像情報があるかか判定
    $hasFile = isset($request['image']) && is_array($request['image']) && $request['image']['error'] === UPLOAD_ERR_OK;
    // 一時保存した画像があるか判定
    $hasTempImage = !empty(SessionService::get('tmp_image_path'));

    switch ($mode) {
      case 'create':
        if($hasFile) {
          $this->imageValidate($request['image']);
        } elseif(! $hasTempImage) {
          $this->errors['image'] = '画像を選択してください';
        }
        break;
      
      case 'edit':
        if($hasFile) {
          $this->imageValidate($request['image']);
        } elseif(empty($request['current_filename']) && ! $hasTempImage) {
          $this->errors['image'] = '画像を選択してください';
        }
        break;
      
      default:
        ErrorHandler::log("未定義のバリデーションモードです: {$mode}");
        throw new \InvalidArgumentException("未定義のバリデーションモードです: {$mode}");
    }

    return empty($this->getErrors());
  }

  protected function imageValidate(array $data)
  {
    if($this->isUploadedFile('image', $data)) {
      if($this->hasValidExtension('image', $data)) {
        if($this->hasValidImageMime('image', $data)) {
          $this->isWithinSizeLimit('image', $data, IMG_MAX);
        }
      }
    }
  }
}