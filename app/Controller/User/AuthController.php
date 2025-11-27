<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Services\Auth\LogoutService;
use App\Services\Auth\UserLoginService;
use App\Services\Core\SessionService;
use App\Services\Helper\UrlHelper;
use App\Services\Validation\UserLoginValidation;

class AuthController extends BaseController
{
  public function loginForm()
  {
    $backUrl = $this->request['login_backUrl'] ?? '';
    if($backUrl !== '') {
      $backUrl = rawurldecode((string)$backUrl);

      if(UrlHelper::isRelative($backUrl)) {
        SessionService::set('login.back', $backUrl);
      }
    }
    $this->render(APP_PATH . '/Views/user/auth/login.php', [
      'backUrl' => SessionService::get('login.back') ?? '',
    ]);
  }

  public function login()
  {
    $validator = new UserLoginValidation();

    if(! $validator->validate($this->request)) {
      SessionService::set('errors', $validator->getErrors());
      redirect('/login', 303);
    }

    $loginService = new UserLoginService();
    if(! $loginService->verifyUser($this->request)) {
      redirect('/login', 303);
    }

    $backUrl = SessionService::flash('login.back') ?? ($this->request['backUrl'] ?? '/');
    $backUrl = urldecode((string)$backUrl);

    if (! UrlHelper::isRelative($backUrl)) {
        $backUrl = '/';
    }

    redirect($backUrl, 303);
  }

  public function logout()
  {
    LogoutService::logout();
    redirect('/');
  }

}