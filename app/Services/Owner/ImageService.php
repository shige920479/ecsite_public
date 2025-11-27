<?php
namespace App\Services\Owner;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Models\ItemImage;
use App\Repositories\ItemImageRepository;
use App\Services\Core\SessionService;
use App\Services\Helper\ImageHandler;
use App\Services\Validation\ImageValidation;
use Exception;
use Intervention\Image\ImageManager;

use function PHPUnit\Framework\arrayHasKey;

class ImageService
{
  private ItemImageRepository $imageRepo;
  private ImageHandler $imageHandler;
  private ImageValidation $validator;

  public function __construct()
  {
    $this->imageRepo = new ItemImageRepository(); 
    $this->imageHandler = new ImageHandler();
    $this->validator = new ImageValidation();
  }

  /**
   * アップロードされた画像のバリデーション
   * 通過した画像は、全てtmpフォルダへ移動＋セッションにパスを一時保存
   */
  public function validateAndMoveTmp(array $request): ?array
  {
    $validatedFiles = $this->validator->getValidatedFiles($request);

    // バリデーションエラーを判定
    $uploadedCount = 0;
    foreach($request['image']['name'] as $index => $name) {
      if(! empty($name)) {
        $uploadedCount++;
      }
    }
    $hasError = count($validatedFiles) < $uploadedCount;

    if(! empty($validatedFiles)) {
      foreach($validatedFiles as $index => $file) {
        $tmpPath = $this->imageHandler->saveTempImage($file);
        $def_sort = $file['def_sort'];
        if($tmpPath === null) {
          SessionService::set("errors.image[{$def_sort}]", '画像が正しくアップロードできませんでした');
          $hasError = true;
        } else {
          // 再入力等で画像に変更があった際、tmpフォルダの元画像を削除
          $hasTmpPath = SessionService::get("tmp_image_path.{$def_sort}"); 
          if($hasTmpPath) {
            $this->imageHandler->deleteTempImage($hasTmpPath);
          }
          SessionService::set("tmp_image_path.{$def_sort}", $tmpPath);
        }
      }
    }

    if($hasError) {
      SessionService::set('errors', $this->validator->getErrors());
      return null;
    }

    return $validatedFiles;
  }

  public function storeImage(array $storePaths, int $itemId)
  {
    DbConnect::beginTransaction();
    try {
      $sort_order = 1;
      $uploadedFileNames = [];// ロールバック時のuploadsフォルダ内の削除対象画像
      foreach($storePaths as $index => $tmpPath) {
        $filename = $this->imageHandler->promoteTmpToFinal($tmpPath, 'item-images');
        if($filename === null) {
          SessionService::set("errors.image[$index]", '画像が正しくアップロードされていません');
          throw new Exception('画像のアップロードに失敗');
        }
        $uploadedFileNames[] = $filename;
        $imageData = [
          'item_id' => $itemId,
          'filename' => $filename,
          'sort_order' => $sort_order
        ];
        $this->imageRepo->insert($imageData);
        $sort_order++;
      }
      DbConnect::commitTransaction();
      SessionService::forget('tmp_image_path');
      foreach($storePaths as $tmpPath) {
        $this->imageHandler->deleteTempImage($tmpPath);
      }

      return true;

    } catch(Exception $e) {
      DbConnect::rollbackTransaction();
      SessionService::forget('tmp_image_path');
      foreach($storePaths as $tmpPath) {
        $this->imageHandler->deleteTempImage($tmpPath); //　エラー時はログ取得するだけでスルー
      }
      foreach($uploadedFileNames as $uploadedfile) {
        $this->imageHandler->deleteUploadedImage($uploadedfile, 'item-images'); //　エラー時はログ取得するだけでスルー
      }
      SessionService::set('errors.image', 'アップロードに失敗しました、初めからやり直してください');
      return false;
    }
  }

  public function updateImage(int $itemId, array $request)   //$item_id　必要か最後に判断
  {
    $imageFiles = [];

    foreach($request['image']['name'] as $index => $name) {
      $imageNumber = $request['def_sort'][$index];
      $imageFile = [
        'image_id' => $request['image_id'][$index] ?? null,
        'tmp_image_path' => SessionService::get("tmp_image_path.{$imageNumber}") ?? null,
        'error' => $request['image']['error'][$index],
        'sort_order' => $request['sort_order'][$index] ?? null,
        'def_sort' => $request['def_sort'][$index],
        'def_filename' => $request['def_filename'][$index] ?? null,
        'name' => $request['image']['name'][$index]
      ];
      
      $imageFiles[] = $imageFile;
    }

    try {
      DbConnect::beginTransaction();

      $defImages = [];
      $tmpImages = [];
      $uploadedFileNames = []; // ロールバック時に削除する画像ファイル

      foreach($imageFiles as $file) {
        if(empty($file['sort_order'])) {
          continue;
        } elseif($file['tmp_image_path'] !== null && !empty($file['image_id'])) {
          $filename = $this->imageHandler->promoteTmpToFinal($file['tmp_image_path'], 'item-images');
          if($filename === null) {
            $index = $file['def_sort'];
            SessionService::set("errors.image[$index]", '画像が正しくアップロードされていません');
            throw new Exception('画像のアップロードに失敗');
          }
          $uploadedFileNames[] = $filename; // ロールバック時用
          $imageData = [
            'filename' => $filename,
            'sort_order' => $file['sort_order'],
            'id' => $file['image_id']
          ];
          $this->imageRepo->update($imageData);
          list($defImages[], $tmpImages[]) = [$file['def_filename'], $file['tmp_image_path']];

        } elseif($file['tmp_image_path'] !== null) {
          $filename = $this->imageHandler->promoteTmpToFinal($file['tmp_image_path'], 'item-images');
          if($filename === null) {
            $index = $file['def_sort'];
            SessionService::set("errors.image[$index]", '画像が正しくアップロードされていません');
            throw new Exception('画像のアップロードに失敗');
          }
          $uploadedFileNames[] = $filename; // ロールバック時用
          $imageData = [
            'item_id' => $itemId,
            'filename' => $filename,
            'sort_order' => $file['sort_order']
          ];
          $this->imageRepo->insert($imageData);
          $tmpImages[] = $file['tmp_image_path'];

        } elseif(!empty($file['image_id']) && (int)$file['sort_order'] !== ((int)$file['def_sort'] + 1)) {
          $imageData = [
            'id' => $file['image_id'],
            'sort_order' => $file['sort_order']
          ];
          $this->imageRepo->updateSortOrder($imageData);

        }
      }
      DbConnect::commitTransaction();
      
      foreach($defImages as $defImageFileName) {
        $this->imageHandler->deleteUploadedImage($defImageFileName, 'item-images'); // エラー時はログ取得のみ
      }
      foreach($tmpImages as $tmpImagePath) {
        $this->imageHandler->deleteTempImage($tmpImagePath); // エラー時はログ取得のみ
      }
      SessionService::forget('tmp_image_path');

      return true;
    
    } catch (Exception $e) {
      DbConnect::rollbackTransaction();
      // アップロード済みファイルの削除
      foreach($uploadedFileNames as $uploadedfile) {
        $this->imageHandler->deleteUploadedImage($uploadedfile, 'item-images'); // エラー時はログ取得のみ
      }
      // tmpフォルダ内の画像とセッションの画像パスを削除
      $storePaths = SessionService::get('tmp_image_path');
      foreach($storePaths as $tmpPath) {
        $this->imageHandler->deleteTempImage($tmpPath); //　エラー時はログ取得するだけでスルー
      }
      SessionService::forget('tmp_image_path');

      SessionService::set('errors.image', 'エラーが発生したため、画像の登録を初期化しました。もう一度やり直してください。');
      return false;
    }
  }


  public function deleteImage(int $image_id)
  {
    try {
      return $this->imageRepo->delete($image_id);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function hasAtLeastOneUpdated(array $request)
  {
    $updated = true;
    // アップロード有無判定 ture:有、false:無
    $hasUploads = $this->validator->hasUploadImage($request);

    // 一時保存ファイルが有るか判定  ture:有、false:無
    $hasTmpPath = ! empty(SessionService::get('tmp_image_path'));

    // sort_orderの変更有無判定 ture:有、false:無
    $hasChangeSort = false;
    if(isset($request['sort_order'])) {
      $hasChangeSort = $this->hasChangeSort($request);
    }

    if(! $hasUploads && ! $hasTmpPath && ! $hasChangeSort) {
      $updated = false;
    }
    return $updated;
  }

  public function hasChangeSort(array $request): bool
  {
    $hasChangeSort = false;
    foreach($request['sort_order'] as $index => $value) {
      if(empty($value)) continue;
      if((int)$value !== ((int)$request['def_sort'][$index] + 1)) {
        $hasChangeSort = true;
        break;
      }
    }
    return $hasChangeSort;
  }
}