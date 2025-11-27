<?php
namespace App\Services\Auth;

use App\Exceptions\ErrorHandler;
use App\Models\TemporaryUser;
use App\Models\User;
use App\Repositories\TemporaryUserRepository;
use App\Repositories\UserRepository;
use App\Services\Core\SessionService;
use Carbon\Carbon;
use Exception;

class UserRegisterService
{
  private TemporaryUserRepository $tempRepo;
  private UserRepository $userRepo;
  

  public function __construct()
  {
    $this->tempRepo = new TemporaryUserRepository();
    $this->userRepo = new UserRepository();
  }

  public function registerTemporaryUser(array $request)
  {
    try {
      $existing = $this->tempRepo->findByEmail($request['email']);
      // 有効期限内の登録が既に存在する場合は false
      if($existing !== null && ! $existing->isExpired()) {
        SessionService::set('register.email', $existing->email);
        SessionService::set('register.verification_code', $existing->verification_code);
        return;
      }

      $user = new TemporaryUser([
        'email' => $request['email'],
        'verification_code' => strval(rand(100000, 999999)),
        'expires_at' => Carbon::now()->addMinutes(30)->format('Y-m-d H:i:s')
      ]);
      SessionService::set('register.email', $user->email);
      SessionService::set('register.verification_code', $user->verification_code);
      return $this->tempRepo->insert($user);
    
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function verifyCode(array $request)
  {
    try {
      $existing = $this->tempRepo->findByEmail($request['email']);
      if($existing === null || $existing->verification_code !== $request['verification_code']) {
        return false;
      } else {
        return $existing;
      }
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function registerUser()
  {
    try {
      $input = SessionService::get('register');
      $existing = $this->userRepo->findByEmail($input['email']);
      if($existing !== null) return false;

      $user = User::createForRegister($input);
  
      return $this->userRepo->insert($user);

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }

  }
}
