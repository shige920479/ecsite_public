<?php
namespace App\Controller\Owner;

use App\Controller\BaseController;
use App\Middleware\AuthMiddleware;
use App\Services\Auth\LogoutService;
use App\Services\Auth\OwnerLoginService;
use App\Services\Core\SessionService;
use App\Services\Validation\OwnerLoginValidation;

class AuthController extends BaseController
{
  public function loginForm(): void
  {
    AuthMiddleware::redirectIfAuthenticated('owner');
    $this->render(APP_PATH . '/Views/owner/login.php');
  }

  public function login()
  {
    $validator = new OwnerLoginValidation();
    if(! $validator->validate($this->request)) {
      SessionService::set('errors', $validator->getErrors());
      SessionService::set('old', $validator->getOld());
      redirect('/owner/login');
    }
    $ownerLoginService = new OwnerLoginService();
    if(! $ownerLoginService->verifyUser($this->request)) {
      redirect('/owner/login');
    }
    redirect('/owner/home');
  }

  public function logout()
  {
    if(isset($_SESSION['tmp_image_path'])) clearTmpImageSessionAndFile();
    LogoutService::logout();
    redirect('/owner/login');
  }

}