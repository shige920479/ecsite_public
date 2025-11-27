<?php
namespace App\Services\Validation;

class OwnerRegisterValidation extends BaseValidation
{
  public function validate(array $request)
  {
    $name = isset($request['name']) ? trim((string)$request['name']) : null;
    $email = isset($request['email']) ? trim((string)$request['email']) : null;
    $password = $request['password'] ?? null;
    $confirm = $request['confirm_password'] ?? null;

    if($this->required('name', $name)) {
      $this->maxLength('name', $name, 50); 
    }

    if($this->required('email', $email)) {
      $this->email('email', $email);
    }
    $passwordOk = $this->required('password', $password);
    $confirmOk = $this->required('confirm_password', $confirm);

    if($passwordOk && $confirmOk){
      if($this->confirm('password', $password, $confirm)) {
        $this->password('password', $password);
      }
    }

    return (empty($this->getErrors()));
  }
}