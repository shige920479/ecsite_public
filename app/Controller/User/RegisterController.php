<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Services\Auth\UserRegisterService;
use App\Services\Core\SessionService;
use App\Services\Validation\UserRegisterValidation;

class RegisterController extends BaseController
{
  public function temporary()
  {
    SessionService::clear('register');
    $this->render(APP_PATH . '/Views/user/auth/temporary.php');
  }
  
  public function temporarySend()
  {
    $validator = new UserRegisterValidation();
    if(! $validator->tempUserValidate($this->request)) {
      SessionService::set('errors', $validator->getErrors());
      SessionService::set('old', $validator->getOld());
      redirect('/temporary');
    }
    $registerTemporary = new UserRegisterService();
    $registerTemporary->registerTemporaryUser($this->request);
    redirect('/guide');
    
  }

  public function showGuide()
  { 
    if(! SessionService::get('register')) {
      ErrorHandler::log('不正なアクセス、認証用のセッション情報なし');
      ErrorHandler::redirectWithCode(400);
    }
    $this->render(APP_PATH . '/Views/user/auth/guide.php');
  }
  
  public function checkCode()
  {
    $validator = new UserRegisterValidation();
    if(! $validator->codeValidate($this->request)) {
      $_SESSION['errors'] = $validator->getErrors();
      redirect('/guide');
    } 
    $codeCheck = new UserRegisterService();
    $existing = $codeCheck->verifyCode($this->request);
    if(! $existing) {
      SessionService::set('errors.verification_code', "コードが正しくない様です。再入力願います");
      redirect('/guide');
    } elseif($existing->isExpired()) {
      SessionService::set('errors.verification_code', "入力期限が切れております。再度仮登録をお願いします");
      SessionService::forget('register');
      redirect('/temporary');
    }

    SessionService::forget('register.verification_code');
    redirect('/userRegister');
  }  

  public function showForm()
  {
    SessionService::clear(['register.name', 'register.password']);
    if(! SessionService::get('register.email')) {
      ErrorHandler::log('不正なアクセス、認証用のセッション情報なし');
      ErrorHandler::redirectWithCode(400);
    }
    $this->render(APP_PATH . '/Views/user/auth/register.php');
  }

  public function sendForm()
  {
    $validator = new UserRegisterValidation();
    if(! $validator->userRegisterValidate($this->request)) {
    SessionService::set('errors', $validator->getErrors());
    SessionService::set('old', $validator->getOld());

    redirect('/userRegister');
   }
    $registerData = array_filter($this->request, fn($key) => in_array($key, ['name', 'password']), ARRAY_FILTER_USE_KEY);
    $registerData = array_merge($registerData, SessionService::get('register'));
    SessionService::set('register', $registerData);
    
    redirect('/confirmInput');
  }

  public function confirmInput()
  {
    $this->render(APP_PATH . '/Views/user/auth/confirm-account.php');
  }

  public function sendInput()
  {
    $register = new UserRegisterService();
    if(! $register->registerUser()) {
      SessionService::set('errors.email', 'このアカウントは既に登録済です');
      SessionService::forget('register');
      redirect('/temporary');
    } else {
      SessionService::forget('register');
      redirect('/completeRegister');
    }
  }

  public function complete()
  {
    $this->render(APP_PATH . '/Views/user/auth/complete.php');
  }
}