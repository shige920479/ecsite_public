<?php
namespace App\Services\Validation;

class OwnerUpdateValidation extends BaseValidation
{
  public function validate(array $request): bool
  {
    if($this->required('name', $request['name'])) {
      $this->maxLength('name', $request['name'], 50);
    } 
    if($this->required('email', $request['email'])) {
      $this->email('email', $request['email']);
    }
    return empty($this->getErrors());
  }

}