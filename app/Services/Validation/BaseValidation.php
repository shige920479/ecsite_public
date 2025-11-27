<?php
namespace App\Services\Validation;

use App\Services\Validation\traits\FormatRulesTrait;

abstract class BaseValidation
{
  use FormatRulesTrait;
  
  public array $errors = [];
  public array $old = [];

}