<?php
namespace App\Services\Auth;

use App\Repositories\AdminRepository;
use App\Services\Security\PasswordService;
use App\Services\Core\SessionService;
use App\Services\Validation\AdminLoginValidation;

class AdminLoginService
{
  private AdminRepository $repository;

  public function __construct()
  {
    $this->repository = new AdminRepository;
  }

  public function verifyUser(array $request)
  {
    $admin = $this->repository->findByEmail($request['email']);
    if($admin === null) {
      SessionService::set('errors.email', 'メールアドレスが登録されていません');
      return false;
    } elseif(! PasswordService::verifyPassword($request['password'], $admin->password)) {
      SessionService::set('errors.email', '入力情報が正しくありません、再度入力願います');
      return false;
    }
    
    SessionService::loginSessionGenerate($admin, 'admin');
    return true;
  }

}