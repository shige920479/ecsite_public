<?php
namespace App\Services\Validation;

use App\Services\Core\SessionService;
use App\Services\Validation\traits\FormatRulesTrait;

class AdminLoginValidation extends BaseValidation
{
  public function validate(array $request) {
    $this->required('email', $request['email']);
    $this->required('password', $request['password']);

    return empty($this->getErrors());
  }
}