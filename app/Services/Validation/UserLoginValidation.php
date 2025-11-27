<?php
namespace App\Services\Validation;

class UserLoginValidation extends BaseValidation
{
  public function validate(array $request)
  {
    $this->required('email', $request['email'] ?? '');
    $this->required('password', $request['password'] ?? '');
    
    return empty($this->getErrors());
  }
}