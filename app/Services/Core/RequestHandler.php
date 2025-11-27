<?php
namespace App\Services\Core;

use App\Exceptions\ErrorHandler;

class RequestHandler
{
  private RequestValidator $validator;
  private RequestSanitizer $sanitizer;

  public function __construct(RequestValidator $validator, RequestSanitizer $sanitizer)
  {
    $this->validator = $validator;
    $this->sanitizer = $sanitizer;
  }

  public function getRequest(): array
  {

    $post = $_POST ?? [];
    $get = $_GET ?? [];
    $files = $_FILES ?? [];

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->validator->validateToken($post);
    }
    
    $params = array_merge($get, $post);

    $normalized = $this->sanitizer->normalize($params);
    $cleaned = $this->sanitizer->sanitize($normalized);

    return array_merge($cleaned, $files);
  }
}