<?php

use App\Models\Shop;
use App\Services\Validation\ShopValidation;
use PHPUnit\Framework\TestCase;

class ShopValidationTest extends TestCase
{
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Services\Validation\ShopValidation */
  private ShopValidation $validator;

  protected function setUp(): void
  {
    if(session_status() !== PHP_SESSION_ACTIVE) {
      @session_start();
    }
    $_SESSION = [];

    $this->validator = $this->getMockBuilder(ShopValidation::class)
                  ->onlyMethods(['imageValidate'])
                  ->disableOriginalConstructor()
                  ->getMock();
  }
  protected function tearDown(): void
  {
    if(! empty($_SESSION['tmp_image_path'])) {
      unset($_SESSION['tmp_image_path']);
    }
  }

  private function okRequest(): array
  {
    return [
        'name' => 'shop1',
        'information' => '説明',
        'is_selling' => 1,
    ];
  }

  private function okFile(): array
  {
    return [
      'name' => 'sample.jpg',
      'type' => 'image/jpeg',
      'tmp_name' => __FILE__,
      'error' => UPLOAD_ERR_OK,
      'size' => 123
    ];
  }
  /** 正常系(create, ファイルあり) */
  public function testValidateCreateWithFileOk(): void
  {
    $request = $this->okRequest();
    $request['image'] = $this->okFile();
    
    $this->validator->expects($this->once())
    ->method('imageValidate')
    ->willReturn(true);
    $result = $this->validator->validate($request, 'create');

    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
  }

  /** 正常系(edit, 現在画像あり) */
  public function testValidateEditWithCurrentFilenameOk(): void
  {
    $request = $this->okRequest();
    $request['current_filename'] = 'current.jpg';

    $result = $this->validator->validate($request, 'edit');

    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
  }
  /** is_selling 未選択 */
  public function testValidateIsSellingMissing(): void
  {
    $request = $this->okRequest();
    unset($request['is_selling']);

    $result = $this->validator->validate($request, 'create');

    $this->assertFalse($result);
    $this->assertArrayHasKey('is_selling', $this->validator->getErrors());
    $this->assertSame('ステータスが選択されておりません', $this->validator->getErrors()['is_selling']);
  }
  /** is_selling 不正値（validBoolean が NG を返す状況を模す） */
  public function testValidateIsSellingInvalidValue():void
  {
    $request = $this->okRequest();
    $request['is_selling'] = 2;

    $result = $this->validator->validate($request, 'create');

    $this->assertFalse($result);
    $this->assertArrayHasKey('is_selling', $this->validator->getErrors());
  }
  /** create: 画像 必須（hasFile=✕ & hasTempImage=✕） */
  public function testValidateCreateImageRequiredWhenNoFileAndNoTemp(): void
  {
    $request = $this->okRequest();
    $_SESSION['tmp_image_path'] = null;

    $result = $this->validator->validate($request, 'create');

    $this->assertFalse($result);
    $this->assertArrayHasKey('image', $this->validator->getErrors());
    $this->assertSame('画像を選択してください', $this->validator->getErrors()['image']);
    $this->assertArrayHasKey('name', $this->validator->getOld());
    $this->assertArrayHasKey('information', $this->validator->getOld());
    $this->assertArrayHasKey('is_selling', $this->validator->getOld());
  }
  /** 正常系 create: 一時画像あれば OK */
  public function testValidateCreateOkWhenTempImageExists():void
  {
    $request = $this->okRequest();
    $_SESSION['tmp_image_path'] = 'tmp/tempimage.jpg';

    $result = $this->validator->validate($request, 'create');

    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
  }
  /** edit: current も temp も無いと画像必須 */
  public function testEditImageRequiredWhenNoFileNoCurrentNoTemp(): void
  {
    $request = $this->okRequest();
    $request['current_filename'] = '';
    $_SESSION['tmp_image_path'] = null;
    
    $result = $this->validator->validate($request, 'edit');

    $this->assertFalse($result);
    $this->assertArrayHasKey('image', $this->validator->getErrors());
    $this->assertSame('画像を選択してください', $this->validator->getErrors()['image']);
  }
  /** 未定義モードは例外 */
  public function testUnknownModeThrows(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->validator->validate($this->okRequest(), 'unknown');
  }

}