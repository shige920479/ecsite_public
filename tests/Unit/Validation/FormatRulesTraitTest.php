<?php
use PHPUnit\Framework\TestCase;
use App\Services\Validation\traits\FormatRulesTrait;
use PHPUnit\Framework\Attributes\DataProvider;

class FormatRulesTraitTest extends TestCase
{
  private $class;

  protected function setUp(): void
  {
    $this->class = new class {
      use FormatRulesTrait;

      public array $errors = [];
      public array $old = [];

      // public function callRequired(string $field, $value): bool
      // {
      //   return $this->required($field, $value);
      // }

      public function call(string $method, ...$args)
      {
        return $this->{$method}(...$args);
      }

      public function getErrors(): array
      {
        return $this->errors;
      }

      public function getOld(): array
      {
        return $this->old;
      }

      protected function isReallyUploaded(string $tmpName): bool
      {
        return $tmpName === '/tmp/testfile';
      }

      protected function getImageMimeType(string $tmpName): string
      {
        return match($tmpName) {
          '/tmp/test_jpeg' => 'image/jpeg',
          '/tmp/test_png' => 'image/png',
          '/tmp/test_gif' => 'image/gif',
          '/tmp/test_txt' => 'text/plain',
          default => 'application/octet-stream',
        };
      }
    };
  }

  public function testRequiredWithValidValue()
  {
    $result = $this->class->call('required', 'name', '山田');
    $this->assertTrue($result);
    $this->assertSame('山田', $this->class->getOld()['name']);
    $this->assertArrayNotHasKey('name', $this->class->getErrors());
  }

  public function testRequiredWithEmptyValue()
  {
    $result = $this->class->call('required', 'email', '');
    $this->assertFalse($result);
    $this->assertArrayHasKey('email', $this->class->getErrors());
    $this->assertSame('必須事項です、入力願います', $this->class->getErrors()['email']);
  }

  public function testRadioOptionsWithValidValue()
  {
    $result = $this->class->call('radioOptions', 'up_down', 'add', ['add', 'reduce']);
    $this->assertTrue($result);
    $this->assertSame('add', $this->class->getOld()['up_down']);
    $this->assertArrayNotHasKey('up_down', $this->class->getErrors());
  }

  public function testRadioOptionsWithInvalidValue()
  {
    $result = $this->class->call('radioOptions', 'up_down', 'same', ['add', 'reduce']);
    $this->assertFalse($result);
    $this->assertArrayHasKey('up_down', $this->class->getErrors());
    $this->assertSame('有効な選択肢を選んでください', $this->class->getErrors()['up_down']);
  }

  public function testRadioOptionsWithNullValue()
  {
    $result = $this->class->call('radioOptions', 'up_down', null, ['add', 'reduce']);
    $this->assertFalse($result);
    $this->assertArrayHasKey('up_down', $this->class->getErrors());
    $this->assertSame('有効な選択肢を選んでください', $this->class->getErrors()['up_down']);
  }

  public function testMaxLengthWithValidValue()
  {
    $result = $this->class->call('maxLength', 'name', 'taro', 10);
    $this->assertTrue($result);
    $this->assertSame('taro', $this->class->getOld()['name']);
    $this->assertArrayNotHasKey('name', $this->class->getErrors());
  }

  public function testMaxLengthWithInvalidValue()
  {
    $result = $this->class->call('maxLength', 'name', 'taroyamadataro', 10);
    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->class->getErrors());
    $this->assertSame('10文字以内で入力願います', $this->class->getErrors()['name']);
  }

  public function testMinLengthWithValidValue()
  {
    $result = $this->class->call('minLength', 'password', 'password123', 8);
    $this->assertTrue($result);
    $this->assertSame('password123', $this->class->getOld()['password']);
    $this->assertArrayNotHasKey('password', $this->class->getErrors());
  }

  public function testMinLengthWithInvalidValue()
  {
    $result = $this->class->call('minLength', 'password', 'pass123', 8);
    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->class->getErrors());
    $this->assertSame('8文字以上で入力願います', $this->class->getErrors()['password']);
  }

  public function testNumericWithValidValue()
  {
    $result = $this->class->call('numeric', 'phone', '123456');
    $this->assertTrue($result);
    $this->assertArrayHasKey('phone', $this->class->getOld());
    $this->assertSame('123456', $this->class->getOld()['phone']);
  }

  public function testNumericWithInvalidValue()
  {
    $result = $this->class->call('numeric', 'phone', '123-456');
    $this->assertFalse($result);
    $this->assertArrayHasKey('phone', $this->class->getErrors());
    $this->assertSame('数値で入力願います', $this->class->getErrors()['phone']);
  }

  public function testEmailWithValidValue()
  {
    $result = $this->class->call('email', 'email', 'test@mail.com');
    $this->assertTrue($result);
    $this->assertArrayHasKey('email', $this->class->getOld());
    $this->assertSame('test@mail.com', $this->class->getOld()['email']);
  }

  public function testEmailWithInvalidValue()
  {
    $result = $this->class->call('email', 'email', 'testmail.com');
    $this->assertFalse($result);
    $this->assertArrayHasKey('email', $this->class->getErrors());
    $this->assertSame('メールアドレスの形式が正しくありません', $this->class->getErrors()['email']);
  }

  public function testGenderWithValidValue()
  {
    $result = $this->class->call('gender', 'gender', 'female');
    $this->assertTrue($result);
    $this->assertArrayHasKey('gender', $this->class->getOld());
    $this->assertSame('female', $this->class->getOld()['gender']);
  }

  public function testGenderWithInvalidValue()
  {
    $result = $this->class->call('gender', 'gender', 'abcd');
    $this->assertFalse($result);
    $this->assertArrayHasKey('gender', $this->class->getErrors());
    $this->assertSame('選択が正しくありません', $this->class->getErrors()['gender']);
  }

  #[DataProvider('validPasswordProvider')]
  public function testPasswordWithValidValue($password)
  {
    $result = $this->class->call('password', 'password', $password);
    $this->assertTrue($result);
  }

  #[DataProvider('invalidPasswordProvider')]
  public function testPasswordWithInvalidValue($password)
  {
    $result = $this->class->call('password', 'password', $password);
    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->class->getErrors());
    $this->assertSame('8～12文字の半角英数字で入力願います', $this->class->getErrors()['password']);
  }
  
  public function testConfirmWithValidValue()
  {
    $result = $this->class->call('confirm', 'password', 'password123', 'password123');
    $this->assertTrue($result);
  }

  public function testConfirmWithInvalidValue()
  {
    $result = $this->class->call('confirm', 'password', 'password123', 'password456');
    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->class->getErrors());
    $this->assertSame('確認用と一致していません、再入力願います', $this->class->getErrors()['password']);
  }

  public function testValidBooleanWithValidValue()
  {
    $result = $this->class->call('validBoolean', 'test', 0);
    $this->assertTrue($result);
    $this->assertArrayHasKey('test', $this->class->getOld());
    $this->assertSame(0, $this->class->getOld()['test']);
  }

  public function testValidBooleanWithInvalidValue()
  {
    $result = $this->class->call('validBoolean', 'test', 2);
    $this->assertFalse($result);
    $this->assertArrayHasKey('test', $this->class->getErrors());
    $this->assertSame('無効な値です', $this->class->getErrors()['test']);
  }

  public function testIsUploadedFileWithValidValue()
  {
    $file = ['tmp_name' => '/tmp/testfile'];
    $result = $this->class->call('isUploadedFile', 'image', $file);
    $this->assertTrue($result);
  }

  public function testIsUploadedFileWithInvalidValue()
  {
    $file = [];
    $result = $this->class->call('isUploadedFile', 'image', $file);
    $this->assertFalse($result);
    $this->assertArrayHasKey('image', $this->class->getErrors());
    $this->assertSame('画像ファイルがアップロードされていません', $this->class->getErrors()['image']);
  }

  public function testHasValidImageMimeWithValidValue()
  {
    $file = ['tmp_name' => '/tmp/test_jpeg'];
    $result = $this->class->call('hasValidImageMime', 'image', $file);
    $this->assertTrue($result);
  }

  public function testHasValidImageMimeWithInvalidValue()
  {
    $file = ['tmp_name' => '/tmp/test_txt'];
    $result = $this->class->call('hasValidImageMime', 'image', $file);
    $this->assertFalse($result);
    $this->assertArrayHasKey('image', $this->class->getErrors());
    $this->assertSame('jpg, png, gif のみアップロード可能です', $this->class->getErrors()['image']);
  }

  public function testHasValidExtensionWithValidValue()
  {
    $file = ['name' => '/abc/def/ghi/image.jpg'];
    $result = $this->class->call('hasValidExtension', 'image', $file);
    $this->assertTrue($result);
  }

  public function testHasValidExtensionWithInvalidValue()
  {
    $file = ['name' => '/abc/def/ghi/image.pdf'];
    $result = $this->class->call('hasValidExtension', 'image', $file);
    $this->assertFalse($result);
    $this->assertArrayHasKey('image', $this->class->getErrors());
    $this->assertSame('許可されていない拡張子です', $this->class->getErrors()['image']);
  }

  public function testIsWithinSizeLimitWithValidValue()
  {
    $file = ['size' => 1023 * 1024];
    $result = $this->class->call('isWithinSizeLimit', 'image', $file, 1024);
    $this->assertTrue($result);
  }

  public function testIsWithinSizeLimitInvalidValue()
  {
    $file = ['size' => 1025 * 1024];
    $result = $this->class->call('isWithinSizeLimit', 'image', $file, 1024);
    $this->assertFalse($result);
    $this->assertArrayHasKey('image', $this->class->getErrors());
    $this->assertSame("1024KB以下の画像をアップロードしてください", $this->class->getErrors()['image']);
  }

  public function testIsAlphanumericAndWithinMaxLengthWithValidValue():void
  {
    $result = $this->class->call('isAlphanumericAndWithinMaxLength', 'slug', 'test', 50);

    $this->assertTrue($result);
    $this->assertArrayHasKey('slug', $this->class->getOld());
    $this->assertSame('test', $this->class->getOld()['slug']);
  }
  public function testIsAlphanumericAndWithinMaxLengthWithInValidValue():void
  {
    $value = str_repeat('a', 51);
    $maxLength = 50;
    $result = $this->class->call('isAlphanumericAndWithinMaxLength', 'slug', $value, $maxLength);

    $this->assertFalse($result);
    $this->assertArrayHasKey('slug', $this->class->getErrors());
    $this->assertSame("2～{$maxLength}文字以内のアルファベットで入力してください", $this->class->getErrors()['slug']);
  }

  public static function validPasswordProvider(): array
  {
    return [
      ['abc12345'],
      ['pass5678'],
      ['X9Y8Z7W6'],
      ['Test0000'],
    ];
  }

  public static function invalidPasswordProvider(): array
  {
    return [
      ['short'],
      ['12345678'],
      ['password'],
      ['あいうえお123'],
      ['abc12345678910'],
      [''],
    ];
  }


}