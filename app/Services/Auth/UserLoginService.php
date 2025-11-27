<?php
namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Security\PasswordService;
use App\Services\Core\SessionService;
use APP\Services\Validation\UserLoginValidation;

class UserLoginService
{
  private UserRepository $repository;

  public function __construct()
  {
    $this->repository = new UserRepository();
  }

  public function verifyUser(array $request): bool
  {
    $user = $this->repository->findByEmail($request['email']);
    if($user === null) {
      SessionService::set('errors.email', 'メールアドレスが違っている様です');
      return false;
    } elseif (! PasswordService::verifyPassword($request['password'], $user->password)) {
      SessionService::set('errors.email', '入力情報が正しくありません、再度入力願います');
      return false;
    }
    SessionService::loginSessionGenerate($user, 'user');
    return true;
  }
}