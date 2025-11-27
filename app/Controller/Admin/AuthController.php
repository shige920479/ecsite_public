<?php
namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Middleware\AuthMiddleware;
use App\Services\Auth\AdminLoginService;
use App\Services\Auth\LogoutService;
use App\Services\Core\SessionService;
use App\Services\Validation\AdminLoginValidation;

class AuthController extends BaseController
{
  
  public function loginForm()
  {
    AuthMiddleware::redirectIfAuthenticated('admin');
    $this->render(APP_PATH . '/Views/admin/login.php');
  }

  public function login()
  {
    $validator = new AdminLoginValidation();
    if(! $validator->validate($this->request)) {
      SessionService::set('errors', $validator->getErrors());
      SessionService::set('old', $validator->getOld());
      redirect('/admin/login');
    }

    $loginService = new AdminLoginService();
    if(! $loginService->verifyUser($this->request)) {
      redirect('/admin/login');
    }
    redirect('/admin/home');
  }

  public function logout()
  {
    LogoutService::logout();
    redirect('/admin/login');
  }
}