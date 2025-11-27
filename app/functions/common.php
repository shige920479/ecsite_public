<?php

use App\Exceptions\ErrorHandler;
use App\Services\Helper\ImageHandler;
use App\Services\Helper\UrlHelper;

function isProduction(): bool
{
  return (APP_ENV ?? 'local') === 'production';
}

function h(?string $string): string
{
  return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function setErrors(): array 
{
  $errors = [];
  if(!empty($_SESSION['errors']) && is_array($_SESSION['errors'])) {
    foreach($_SESSION['errors'] as $key => $value) {
      $errors[$key] = $value;
    }
  }
  unset($_SESSION['errors']);
  return $errors;
}

function setOld(): array 
{
  $old = [];
  if (!empty($_SESSION['old']) && is_array($_SESSION['old'])) {
    foreach ($_SESSION['old'] as $key => $value) {
      $old[$key] = $value;
    }
  }
  unset($_SESSION['old']);
  return $old;
}

function redirect(string $url, ?int $status = null): void
{
  if($status === null) {
    $status = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') ? 303 : 302;
  }
  
  if(UrlHelper::isRelative($url)) {
    $target = UrlHelper::toAbsolute($url);
  } else {
    $target = UrlHelper::isSameOrigin($url) 
              ? UrlHelper::sanitizeHeaderValue($url)
              : UrlHelper::toAbsolute('/');
  }

  header('Location:' . $target, true, $status);
  exit;
}

function only(array $request, array $array): array
{
  foreach($request as $key => $value) {
    if(in_array($key, $array)) {
      $result[$key] = $value;
    }
  }
  return $result;
}

function priceWithTax(int $price): int
{
  return floor($price * (1 + TAX_RATE));
}

function clearTmpImageSessionAndFile(): void
{
  $imageHandler = new ImageHandler();
  $flag = 0;
  if(is_array($_SESSION['tmp_image_path'])) {
    foreach($_SESSION['tmp_image_path'] as $tmpImagePath) {
      $result = $imageHandler->deleteTempImage($tmpImagePath);
      if(! $result) $flag++;
    } 
  } else {
    $result = $imageHandler->deleteTempImage($_SESSION['tmp_image_path']);
    if(! $result) $flag++;
  }
  if($flag === 0) unset($_SESSION['tmp_image_path']);
}