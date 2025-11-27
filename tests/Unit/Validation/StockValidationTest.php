<?php

use App\Services\Validation\StockValidation;
use PHPUnit\Framework\TestCase;

class StockValidationTest extends TestCase
{
  private StockValidation $validator;

  protected function setUp(): void
  {
    $this->validator = new StockValidation();
  }
  protected function okRequest(): array
  {
    return [
      'stock_diff' => 5,
      'up_down' => 'add',
    ];
  }

  /** 正常系 */
  public function testValidateWithValidValue(): void
  {
    $request = $this->okRequest();
    
    $result = $this->validator->validate($request);
    $this->assertTrue($result); 
    $this->assertArrayHasKey('stock_diff', $this->validator->getOld());
    $this->assertArrayHasKey('up_down', $this->validator->getOld());
  }
  // stock_diff=無し
  public function testValidateWithStockDiffMissing(): void
  {
    $request = $this->okRequest();
    unset($request['stock_diff']);

    $result = $this->validator->validate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('stock_diff', $this->validator->getErrors());
  }
  // up_down=無
  public function testValidateWithUpDownMissing(): void
  {    
    $request = $this->okRequest();
    unset($request['up_down']);

    $result = $this->validator->validate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('up_down', $this->validator->getErrors());
  }

  // reason=有、文字数オーバー
  public function testValidateWithReasonOverLength(): void
  {
    $request = $this->okRequest();
    $request['reason'] = str_repeat('a', 101);

    $result = $this->validator->validate($request);
    $this->assertFalse($result);
    $this->assertArrayHasKey('reason', $this->validator->getErrors());
  }
}