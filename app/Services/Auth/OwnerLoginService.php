<?php
namespace App\Services\Auth;

use App\Repositories\OwnerRepository;
use App\Services\Security\PasswordService;
use App\Services\Core\SessionService;

class OwnerLoginService
{
  private OwnerRepository $repository;

  public function __construct()
  {
    $this->repository = new OwnerRepository();
  }

  public function verifyUser(array $request)
  {
    $owner = $this->repository->findByEmail($request['email']);
    if($owner === null) {
      SessionService::set('errors.email', "メールアドレスが登録されていません");
      return false;
    } elseif(! PasswordService::verifyPassword($request['password'], $owner->password)) {
      SessionService::set('errors.email', "入力情報が正しくありません、再度入力願います");
      return false;
    }
    
    SessionService::loginSessionGenerate($owner, 'owner');
    return true;
  }
}