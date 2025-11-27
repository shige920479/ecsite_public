<?php
namespace App\Services\Core;

class RequestSanitizer
{
  public function normalize(array $data): array
  {
    $normalized = [];
    foreach($data as $key => $value) {
      if(is_array($value)) {
        $normalized[$key] = $this->normalize($value);
      } else {
        $val = is_string($value) ? trim($value) : $value;
        $normalized[$key] = ($val === '' ? null : $val);
      }
    }
    return $normalized;
  }

  public function sanitize(array $data): array
  {
    $cleaned = [];
    foreach($data as $key => $value) {
      if(is_array($value)) {
        $cleaned[$key] = $this->sanitize($value);
      } else {
        if(is_string($value)) {
          $encoding = mb_detect_encoding($value, 'UTF-8, SJIS, EUC-JP', true) ?: 'UTF-8';
          $value = mb_convert_encoding($value, 'UTF-8', $encoding);
        }
        $cleaned[$key] = $value;
      }
    }
    return $cleaned;
  }
}