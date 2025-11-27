<?php
namespace App\Services\Auth;

use App\Exceptions\ErrorHandler;
use App\Models\Owner;
use App\Repositories\OwnerRepository;
use App\Services\Security\PasswordService;
use App\Services\Core\SessionService;
use Exception;

class OwnerRegisterService
{
  private OwnerRepository $repository;

  public function __construct()
  {
    $this->repository = new OwnerRepository();
  }

  public function registerOwner(array $request)
  {
    try {
      $exisitng = $this->repository->findByEmail($request['email']);
      if($exisitng !== null) return false;
  
      $input = only($request, ['name', 'email', 'password']);
      $owner = Owner::createForRegister($input);
  
      return $this->repository->insert($owner);

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
    }
}
