<?php
namespace App\Controller;

use App\Exceptions\ErrorHandler;
use App\Services\Core\RequestHandler;
use App\Services\Core\RequestSanitizer;
use App\Services\Core\RequestValidator;
use App\Services\Security\TokenManager;

abstract class BaseController
{
  public array $request;
  protected string $csrfToken;

  public function __construct()
  {
    if (!isset($_POST['mode']) && isset($_GET['webhook_mode'])) {
      $_POST['mode'] = $_GET['webhook_mode'];
    }

    $skipModes = include(APP_PATH . '/config/request.php');
    
    $validator = new RequestValidator($skipModes);
    $sanitizer = new RequestSanitizer();
    $handler = new RequestHandler($validator, $sanitizer);
    $this->request = $handler->getRequest();
    $this->csrfToken = TokenManager::get();
  }

  public function render(string $viewPath, array $data = []): void
  {
    extract($data);
    $csrf_token = $this->csrfToken;
    include $viewPath;
  }

  // protected function checkCsrfToken()
  // {
  //   $headers = getallheaders();
  //   $token = $headers['X-CSRF-TOKEN'] ?? '';
  //   return $token === $_SESSION['token'];
  // }
}