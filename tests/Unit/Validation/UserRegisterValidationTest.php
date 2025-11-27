<?php

use App\Services\Validation\UserRegisterValidation;
use PHPUnit\Framework\TestCase;

class UserRegisterValidationTest extends TestCase
{
  private UserRegisterValidation $validator;

  protected function setUp(): void
  {
    $this->validator = new UserRegisterValidation();
  }

  private function okTempRequest(): array
  {
    return [
      'email' => 'example@mail.com',
      'email_confirm' => 'example@mail.com'
    ];
  }
  
  private function okVerifyRequest(): array
  {
    return [
      'verification_code' => '123123123'
    ];
  }

  private function okRegisterRequest(): array
  {
    return [
      'name' => 'sample',
      'password' => 'password123',
      'confirm_password' => 'password123',
    ];
  }
  // 正常系
  public function testTempUserValidateWithValidValue(): void
  {
    $request = $this->okTempRequest();
    $result = $this->validator->tempUserValidate($request);

    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
  }
  // email_confirm=無し
  public function testTempUserValidateWithEmailConfirmMissing(): void
  {
    $request = $this->okTempRequest();
    unset($request['email_confirm']);

    $result = $this->validator->tempUserValidate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('email_confirm', $this->validator->getErrors());
    $this->assertArrayHasKey('email', $this->validator->getOld());
  }
  // email=形式間違い
  public function testTempUserValidateWithInvalidEmail(): void
  {
    $request = $this->okTempRequest();
    $request['email'] = 'example.aaa.com';

    $result = $this->validator->tempUserValidate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('email', $this->validator->getErrors());
  }
  // email_confirm=形式間違い
  public function testTempUserValidateWithInvalidEmailConfirm(): void
  {
    $request = $this->okTempRequest();
    $request['email_confirm'] = 'example.aaa.com';

    $result = $this->validator->tempUserValidate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('email_confirm', $this->validator->getErrors());
    $this->assertArrayHasKey('email', $this->validator->getOld());
  }
  // email_confirm=emailと差異有り
  public function testTempUserValidateWithDifferentEmails():void
  {
    $request = $this->okTempRequest();
    $request['email_confirm'] = 'example1@mail.com';

    $result = $this->validator->tempUserValidate($request);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('email_confirm', $this->validator->getErrors());
  }
  // 正常系
  public function testCodeValidateWithValidValue(): void
  {
    $request = $this->okVerifyRequest();

    $result = $this->validator->codeValidate($request);

    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
  }
  // verification_code=無し
  public function testCodeValidateWithCodeMissing(): void
  {
    $request = [];

    $result = $this->validator->codeValidate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('verification_code', $this->validator->getErrors());
  }
  // verification_code=空文字
  public function testCodeValidateWithEmptyString(): void
  {
    $request = ['verification_code' => ''];

    $result = $this->validator->codeValidate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('verification_code', $this->validator->getErrors());
  }

  // 正常系
  public function testUserRegisterValidateWithValidValue(): void
  {
    $request = $this->okRegisterRequest();

    $result = $this->validator->userRegisterValidate($request);

    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
  }
  // name=無し
  public function testUserRegisterValidateWithNameMissing(): void
  {
    $request = $this->okRegisterRequest();
    unset($request['name']);

    $result = $this->validator->userRegisterValidate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->validator->getErrors());
    $this->assertArrayNotHasKey('password', $this->validator->getold());
  }
  // name=maxLengthオーバー
  public function testUserRegisterValidateWithNameLengthOver(): void
  {
    $request = $this->okRegisterRequest();
    $request['name'] = str_repeat('a', 50);
    $result = $this->validator->userRegisterValidate($request);
    $this->assertTrue($result);

    $ngRequest = $this->okRegisterRequest();
    $ngRequest['name'] = str_repeat('a', 51);
    $result = $this->validator->userRegisterValidate($ngRequest);
    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->validator->getErrors());
  }

  // password違い
  public function testUserRegisterValidateWithDifferentPassword(): void
  {
    $request = $this->okRegisterRequest();
    $request['confirm_password'] = 'password1234';

    $result = $this->validator->userRegisterValidate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->validator->getErrors());
    $this->assertArrayNotHasKey('confirm_password', $this->validator->getold());
  }
  // password=弱い
  public function testUserRegisterValidateWithWeakPassword(): void
  {
      $request = $this->okRegisterRequest();
      $request['password'] = $request['confirm_password'] = 'short12';

      $result = $this->validator->userRegisterValidate($request);
      
      $this->assertFalse($result);
      $this->assertArrayHasKey('password', $this->validator->getErrors());
  }
}