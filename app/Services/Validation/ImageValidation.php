<?php
namespace App\Services\Validation;

use App\Services\Core\SessionService;

class ImageValidation extends BaseValidation
{
  // アップロード画像の一括バリデーション
  public function isValid(array $file, string $field): bool
  {
      if ($file['error'] === UPLOAD_ERR_OK) {
          if (! $this->isUploadedFile($field, $file)) {
              return false;
          }
          if (! $this->hasValidExtension($field, $file)) {
              return false;
          }
          if (! $this->hasValidImageMime($field, $file)) {
              return false;
          }
          if (! $this->isWithinSizeLimit($field, $file, IMG_MAX)) {
              return false;
          }
          return true;
      }
      return false;
  }

  public function getValidatedFiles(array $request): array
  {
      $validated = [];
      foreach($request['image']['name'] as $index => $name) {
        if(empty($name)) {
          continue;
        }
        $file = [
            'image_id' => $request['image_id'][$index] ?? null,
            'name' => $request['image']['name'][$index],
            'type' => $request['image']['type'][$index],
            'tmp_name' => $request['image']['tmp_name'][$index],
            'error' => $request['image']['error'][$index],
            'size' => $request['image']['size'][$index],
            'sort_order' => $request['sort_order'][$index] ?? null,
            'def_sort' => $request['def_sort'][$index] ?? null
        ];
        
        $defSort = $request['def_sort'][$index];
        $field = "image[$defSort]";

        if (! $this->isValid($file, $field)) {
          continue;
        }
        $validated[$defSort] = $file;
      }

      return $validated;
  }

  public function hasAtLeastOneUploadedImage(array $request): bool
  {
    foreach ($request['image']['error'] as $index => $error) {
        if ($error === UPLOAD_ERR_OK) {
            return true;
        }
        $tmp = SessionService::get("tmp_image_path.$index") ?? null;
        if (!empty($tmp)) {
            return true;
        }
    }
    $this->errors['image'] = '画像を1枚以上選択してください';
    return false;
  }

  public function hasUploadImage(array $request): bool
  {
    foreach($request['image']['error'] as $index => $error) {
      if($error === UPLOAD_ERR_OK) {
        return true;
      }
    }
    return false;
  }

}