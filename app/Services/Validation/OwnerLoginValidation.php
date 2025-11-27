<?php
namespace App\Services\Validation;

class OwnerLoginValidation extends BaseValidation
{
  public function validate(array $request) {
    $this->required('email', $request['email']);
    $this->required('password', $request['password']);

    return empty($this->getErrors());
  }
}