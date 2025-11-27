<?php
namespace App\Services\Admin;

use App\Exceptions\ErrorHandler;
use App\Models\Owner;
use App\Repositories\OwnerRepository;
use Exception;

class AdminService
{
  public OwnerRepository $ownerRepo;

  public function __construct()
  {
    $this->ownerRepo = new OwnerRepository(); 
  }

  public function getAllOwners()
  {
    try {
      return $this->ownerRepo->getAll();
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function getOwner(int $id)
  {
    try {
      return $owner = $this->ownerRepo->findById($id);    
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function updateOwner(int $id, array $request)
  {
    try {
      $existing = $this->ownerRepo->findById($id);
      if($existing === null) {
        return ['succecc' => false, 'error' => 'not_found'];
      }
      if($existing['name'] ===$request['name'] && $existing['email'] === $request['email']) {
        return ['success' => false, 'error' => 'no_changes'];
      }
      $conflict = $this->ownerRepo->findByEmail($request['email']);
      if($conflict !== null && $conflict->id !== $id){
        return ['success' => false, 'error' => 'email_exists'];
      } 

      $owner = new Owner($request);
      $this->ownerRepo->update($id, $owner);
    
      return ['success' => true];

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

}