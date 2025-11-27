<?php
namespace App\Services\Validation;

class UserRegisterValidation extends BaseValidation
{
  public function tempUserValidate(array $request): bool
  {
    if ($this->required('email', $request['email'] ?? '') && 
      $this->required('email_confirm', $request['email_confirm'] ?? '')) {
        if($this->email('email', $request['email']) &&
          $this->email('email_confirm', $request['email_confirm'])) {
            $this->confirm('email_confirm', $request['email_confirm'], $request['email']);
          }
    }

    return empty($this->getErrors());
  }

  public function codeValidate(array $request): bool
  {
    $this->required('verification_code', $request['verification_code'] ?? '');
    return empty($this->getErrors());
  }

  public function userRegisterValidate(array $request): bool
  {
    if($this->required('name', $request['name'] ?? '')) {
      $this->maxLength('name', $request['name'], 50);
    }
    $passwordOk = $this->required('password', $request['password'] ?? '');
    $confirmOk = $this->required('confirm_password', $request['confirm_password'] ?? '');
    if($passwordOk && $confirmOk) {
      if($this->confirm('password', $request['password'], $request['confirm_password'])) {
        $this->password('password', $request['password']);
      }
    }
    return empty($this->getErrors());
  }
}