<?php
namespace App\Services\Helper;

use App\Services\Core\SessionService;
use App\Services\Validation\ShopValidation;

class ShopFormHelper
{
  private ShopValidation $validator;
  private ImageHandler $imageHandler;

  public function __construct(ShopValidation $validator, ImageHandler $imageHandler)
  {
    $this->validator = $validator;
    $this->imageHandler = $imageHandler;
  }

  public function handleValidationAndTempImage(array $request, string $mode = 'create', ?int $shopId = null): ?string
  {
    $hasNewUpload = isset($request['image']) && is_array($request['image']) &&
      isset($request['image']['error']) && $request['image']['error'] === UPLOAD_ERR_OK;
    $tempPath = SessionService::get('tmp_image_path') ?? null;

    if(! $this->validator->validate($request, $mode)) {
      if($hasNewUpload && empty($validator->errors['image'])) {
        if($tempPath !== null && str_starts_with($tempPath, '/tmp/')) {
          $this->imageHandler->deleteTempImage($tempPath);
        }
        $tempImagePath = $this->imageHandler->saveTempImage($request['image']);
        SessionService::set('tmp_image_path', $tempImagePath);
      }

      SessionService::set('errors', $this->validator->getErrors());
      SessionService::set('old', $this->validator->getOld());

      return $mode === 'edit' ? "/owner/shop/{$shopId}/edit" : '/owner/registerShop';

    }
    return null;
  }
}