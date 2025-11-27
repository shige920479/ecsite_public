<?php

use App\Services\Validation\OwnerRegisterValidation;
use PHPUnit\Framework\TestCase;

class OwnerRegisterValidationTest extends TestCase
{
  private OwnerRegisterValidation $validator;

  protected function setUp(): void
  {
    $this->validator = new OwnerRegisterValidation();
  }

  public function testValidateWithValidValue(): void
  {
    $request = [
      'name' => 'test',
      'email' => 'test@mail.com',
      'password' => 'test12345',
      'confirm_password' => 'test12345'
    ];

    $result =$this->validator->validate($request);

    $this->assertTrue($result);
    $this->assertArrayHasKey('name', $this->validator->getOld());
    $this->assertArrayHasKey('email', $this->validator->getOld());
    $this->assertArrayNotHasKey('password', $this->validator->getOld());
    $this->assertArrayNotHasKey('confirm_password', $this->validator->getOld());
  }

  public function testValidateMissingNameEmailPasswordConfirmPassword(): void
  {
    $request = [
      // 'name' => 'test',
      // 'email' => 'test@mail.com',
      // 'password' => 'test12345',
      // 'confirm_password' => 'test12345'
    ];

    $result = $this->validator->validate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->validator->getErrors());
    $this->assertArrayHasKey('email', $this->validator->getErrors());
    $this->assertArrayHasKey('password', $this->validator->getErrors());
    $this->assertArrayHasKey('confirm_password', $this->validator->getErrors());
  }

  public function testValidateWithInvalidEmail(): void
  {
    $request = [
      'name' => 'test',
      'email' => 'testmail.com',
      'password' => 'test12345',
      'confirm_password' => 'test12345'
    ];

    $result = $this->validator->validate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('email', $this->validator->getErrors());
  }

  public function testValidateWithMissingConfirmPassword(): void
  {
    $request = [
      'name' => 'test',
      'email' => 'testmail.com',
      'password' => 'test12345',
      // 'confirm_password' => 'test12345'
    ];

    $result = $this->validator->validate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('confirm_password', $this->validator->getErrors());
  }

  public function testValidateMismatchPassword(): void
  {
    $request = [
      'name' => 'test',
      'email' => 'test@mail.com',
      'password' => 'test12345',
      'confirm_password' => 'test12346'
    ];

    $result = $this->validator->validate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->validator->getErrors());
    $this->assertArrayNotHasKey('confirm_password', $this->validator->getErrors());
  }

  public function testValidateWithInvalidPassword(): void
  {
    $request = [
      'name' => 'test',
      'email' => 'test@mail.com',
      'password' => '12345678',
      'confirm_password' => '12345678'
    ];

    $result = $this->validator->validate($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->validator->getErrors());
    $this->assertArrayNotHasKey('confirm_password', $this->validator->getErrors());
  }

  public function testValidateWithWhitespaceOnlyNameEmail(): void
  {
      $req = [
          'name' => '   ',
          'email' => '   ',
          'password' => 'Passw0rd!',
          'confirm_password' => 'Passw0rd!',
      ];
      $this->assertFalse($this->validator->validate($req));
      $errors = $this->validator->getErrors();
      $this->assertArrayHasKey('name', $errors);
      $this->assertArrayHasKey('email', $errors);
  }
}
