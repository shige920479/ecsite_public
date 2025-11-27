<?php
namespace App\Services\Validation;

class StockValidation extends BaseValidation
{
  public function validate(array $request)
  {
    if($this->required('stock_diff', $request['stock_diff'] ?? null)) {
      $this->numeric('stock_diff', $request['stock_diff']);
    }

    $this->radioOptions('up_down', $request['up_down'] ?? null, ['add', 'reduce']);

    if(isset($request['reason']) && $request['reason'] !== '') {
      $this->maxLength('reason', $request['reason'], 100);
    }

    return empty($this->getErrors());
  }

}