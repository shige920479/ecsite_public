<?php
namespace App\Services\Validation\traits;

use App\Repositories\CategoryRepository;
use App\Services\Core\SessionService;
use finfo;

trait FormatRulesTrait
{
  protected function required(string $field, $value)
  {
    if(! isset($value) || trim((string)$value) === '') {
      $this->errors[$field] = "必須事項です、入力願います";
      return false;
    } else {
      if(! in_array($field, ['password', 'confirm_password'])) {
        $this->old[$field] = $value;
      }
      return true;
    }
  }

  protected function radioOptions(string $field, $value, array $validOptions)
  {
    if (!isset($value) || !in_array($value, $validOptions)) {
      $this->errors[$field] = "有効な選択肢を選んでください";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }
  
  protected function maxLength(string $field, $value, int $maxLength)
  {
    if(mb_strlen($value, 'UTF-8') > $maxLength) {
      $this->errors[$field] = "{$maxLength}文字以内で入力願います";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function minLength(string $field, $value, int $minLength)
  {
    if(mb_strlen($value, 'UTF-8') < $minLength) {
      $this->errors[$field] = "{$minLength}文字以上で入力願います";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  /**
   * 1以上の整数か判定（0や0123を弾く）
   */
  protected function numeric(string $field, $value)
  {
    if(! preg_match('/\A[1-9][0-9]*\z/', $value)) {
      $this->errors[$field] = "数値で入力願います";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function email(string $field, $value)
  {
    if(! filter_var($value, FILTER_VALIDATE_EMAIL)) {
      $this->errors[$field] = "メールアドレスの形式が正しくありません";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function gender(string $field, $value)
  {
    if(! in_array($value, ['male', 'female', 'other'])) {
      $this->errors[$field] = "選択が正しくありません";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function password(string $field, $value)
  {
    if(! preg_match("/\A(?=.*?[A-z])(?=.*?\d)[A-z\d]{8,12}+\z/", $value)) {
      $this->errors[$field] = "8～12文字の半角英数字で入力願います";
      return false;
    } else {
      return true;
    }
  }

  protected function confirm(string $field, $value, $confirm_value)
  {
    if($value !== $confirm_value) {
      $this->errors[$field] = '確認用と一致していません、再入力願います';
      return false;
    } else {
      return true;
    }
  }

  protected function validBoolean(string $field, int $value)
  {
    if(! in_array($value, [0, 1], true)) {
      $this->errors[$field] = '無効な値です';
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }


  // 以下、画像関連
  protected function isUploadedFile(string $field, array $file): bool
  {
    if(! isset($file['tmp_name']) || ! $this->isReallyUploaded($file['tmp_name'])) {
      $this->errors[$field] = '画像ファイルがアップロードされていません';
      return false;
    } else {
      return true;
    }
  }
  // ラップ
  protected function isReallyUploaded(string $tmpName): bool
  {
    return is_uploaded_file($tmpName);
  }

  protected function hasValidImageMime(string $field, array $file): bool
  {
    $mime = $this->getImageMimeType($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/gif'];

    if(! in_array($mime, $allowed, true)) {
      $this->errors[$field] = 'jpg, png, gif のみアップロード可能です';
      return false;
    } else {
      return true;
    }
  }
  // ラップ
  protected function getImageMimeType(string $tmpName): string
  {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->file($tmpName);
  }

  protected function hasValidExtension(string $field, array $file): bool
  {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if(! in_array($ext, $allowed, true)) {
      $this->errors[$field] = '許可されていない拡張子です';
      return false;
    } else {
      return true;
    }

  }

  protected function isWithinSizeLimit(string $field, array $file, int $maxKb): bool
  {
    if($file['size'] > $maxKb * 1024) {
      $this->errors[$field] = "{$maxKb}KB以下の画像をアップロードしてください";
      return false;
    } else {
      return true;
    }
  }

  protected function isAlphanumericAndWithinMaxLength(string $field, $value, int $maxLength): bool
  {
    if(! preg_match("/^[a-zA-Z]{2,{$maxLength}}$/", $value)) {
      $this->errors[$field] = "2～{$maxLength}文字以内のアルファベットで入力してください";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  public function getErrors()
  {
    return $this->errors;
  }
  public function getOld()
  {
    return $this->old;
  }
}