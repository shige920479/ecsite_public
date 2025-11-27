<?php
namespace App\Services\Validation;

class CartValidation extends BaseValidation
{
  public function checkQuantity(int $quantity)
  {
    if($this->required('quantity', $quantity)) {
      $this->numeric('quantity', $quantity);
    }
    return empty($this->getErrors());
  }

}