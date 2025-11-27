<?php

use App\Services\Validation\ImageValidation;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

class ImageValidationTest extends TestCase
{
  private string $fixturesDir;

  protected function setUp(): void
  {
    $this->fixturesDir = __DIR__ . '/../fixtures';

    if(! is_dir($this->fixturesDir)) {
      mkdir($this->fixturesDir, 0777, true);
    }
  }

  protected function tearDown(): void
  {
    foreach (glob($this->fixturesDir . '/*.jpg') as $file) {
      unlink($file);
    }
  }

  public function testIsValidCallsIsUploadedFile()
  {
    $mock = $this->getMockBuilder(ImageValidation::class)
      ->onlyMethods([
        'isUploadedFile',
        'hasValidExtension',
        'hasValidImageMime',
        'isWithinSizeLimit'
      ])->getMock();
    
    $mock->method('isUploadedFile')->willReturn(true);
    $mock->method('hasValidExtension')->willReturn(true);
    $mock->method('hasValidImageMime')->willReturn(true);
    $mock->method('isWithinSizeLimit')->willReturn(true);

    $file = [
      'name' => 'sample.jpg',
      'type' => 'image/jpeg',
      'tmp_name' => $this->fixturesDir . '/sample.jpg',
      'error' => UPLOAD_ERR_OK,
      'size' => 500_000,
    ];

    file_put_contents($file['tmp_name'], 'dummy image content');

    $result = $mock->isValid($file, 'image[0]');
    $this->assertTrue($result);

    unset($file['tmp_name']);
  }

  /**
   * 正常系（全ファイルOK）
   */
  public function testGetValidateFilesWithValidFile(): void
  {
    $tmp1 = $this->fixturesDir . '/sample1.jpg';
    $tmp2 = $this->fixturesDir . '/sample2.jpg';
    $tmp3 = $this->fixturesDir . '/sample3.jpg';
    file_put_contents($tmp1, 'dummy data 1');
    file_put_contents($tmp2, 'dummy data 2');
    file_put_contents($tmp3, 'dummy data 3');

    $request = [
      'sort_order' => [2, 3, 1],
      'def_sort' => [3, 1, 2],
      'image_id' => [null, null],
      'image' => [
        'name' => ['sample1.jpg', 'sample2.jpg', 'sample3.jpg'],
        'type' => ['image/jpeg', 'image/jpeg', 'image/jpeg'],
        'tmp_name' => [$tmp1, $tmp2, $tmp3],
        'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK, UPLOAD_ERR_OK],
        'size' => [1000_1000, 1000_1000, 1000_1000]
      ]
    ];

    $mock = $this->getMockBuilder(ImageValidation::class)
      ->onlyMethods(['isValid'])
      ->getMock();

    $mock->method('isValid')->willReturn(true);

    $result = $mock->getValidatedFiles($request);
    
    $this->assertIsArray($result);
    $this->assertCount(3, $result);
    $this->assertArrayHasKey(1, $result);
    $this->assertArrayHasKey(2, $result);
    $this->assertArrayHasKey(3, $result);
    $this->assertSame([3,1,2], array_keys($result));
    $this->assertSame('sample1.jpg', $result[3]['name']);
    $this->assertSame('sample2.jpg', $result[1]['name']);
    $this->assertSame('sample3.jpg', $result[2]['name']);

    unlink($tmp1);
    unlink($tmp2);
  }

  /**
   * 一部NGのファイルを含む（2番目）
   */
  public function testGetValidateFilesWithInvalidFile(): void
  {
    $tmp1 = $this->fixturesDir . '/sample1.jpg';
    $tmp2 = $this->fixturesDir . '/sample2.jpg';
    file_put_contents($tmp1, 'dummy data 1');
    file_put_contents($tmp2, 'dummy data 2');

    $request = [
      'sort_order' => [2, 1],
      'def_sort' => [1, 2],
      'image_id' => [null, null],
      'image' => [
        'name' => ['sample1.jpg', 'sample2.jpg'],
        'type' => ['image/jpeg', 'image/jpeg'],
        'tmp_name' => [$tmp1, $tmp2],
        'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
        'size' => [1000_1000, 1000_1000]
      ]
    ];

    $mock = $this->getMockBuilder(ImageValidation::class)
      ->onlyMethods(['isValid'])->getMock();

    $mock->expects($this->exactly(2))
      ->method('isValid')->willReturnOnConsecutiveCalls(true, false);

    $result = $mock->getValidatedFiles($request);
    
    $this->assertCount(1, $result);
    $this->assertArrayHasKey(1, $result);
    $this->assertArrayNotHasKey(2, $result);

    unlink($tmp1);
    unlink($tmp2);
  }

  /**
   * image['name']が空の場合、スキップするか
   */
  public function testGetValidateFilesWithEmptyName(): void
  {
    $tmp1 = $this->fixturesDir . '/sample1.jpg';
    $tmp2 = $this->fixturesDir . '/sample2.jpg';
    file_put_contents($tmp1, 'dummy tmp1');
    file_put_contents($tmp2, 'dummy tmp2');

    $request = [
      'sort_order' => [2, 1],
      'def_sort' => [1, 2],
      'image_id' => [null, null],
      'image' => [
        'name' => ['', 'sample2.jpg'],
        'type' => ['image/jpeg', 'image/jpeg'],
        'tmp_name' => [$tmp1, $tmp2],
        'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
        'size' => [1000_1000, 1000_1000]
      ]
    ];

    $mock = $this->getMockBuilder(ImageValidation::class)
            ->onlyMethods(['isValid'])->getMock();
    
    $mock->expects($this->once())->method('isValid')->willReturn(true);

    $result = $mock->getValidatedFiles($request);

    $this->assertCount(1, $result);
    $this->assertArrayHasKey(2, $result);
    $this->assertArrayNotHasKey(1, $result);
  }

  /**
   * 画像ファイルが空の場合に、空配列を返すか
   */
  public function testGetValidateFilesWithEmptyFile(): void
  {
    $request = [
      'sort_order' => [],
      'def_sort' => [],
      'image_id' => [],
      'image' => [
        'name' => [],
        'type' => [],
        'tmp_name' => [],
        'error' => [],
        'size' => []
      ]
    ];

    $mock = $this->getMockBuilder(ImageValidation::class)
            ->onlyMethods(['isValid'])->getMock();

    $mock->method('isValid')->willReturn(false);

    $result = $mock->getValidatedFiles($request);

    $this->assertIsArray($result);
    $this->assertCount(0, $result);
  }

  public function testHasAtLeastOneUploadedImageReturnsTrueFromSession(): void
  {
    if(session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    
    $request = [
      'image' => [
        'error' => [UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE]
      ]
    ];
    
    $_SESSION['tmp_image_path'] = [
      1 => '/tmp/tmp_image.jpg'
    ];

    $validator = new ImageValidation();
    $result = $validator->hasAtLeastOneUploadedImage($request);

    $this->assertTrue($result);

    unset($_SESSION['tmp_image_path']);
  }

  public function testHasAtLeastOneUploadedImageHasUploadFile(): void
  {
    $request = [
      'image' => [
        'error' => [UPLOAD_ERR_NO_FILE, UPLOAD_ERR_OK]
      ]
    ];

    $validator = new ImageValidation();
    $result = $validator->hasAtLeastOneUploadedImage($request);

    $this->assertTrue($result);
  }

  public function testHasAtLeastOneUploadedImageWithNoFile(): void
  {
    if(session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    $request = [
      'image' => [
        'error' => [UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE]
      ]
    ];

    $_SESSION['tmp_image_path'] = [];

    $validator = new ImageValidation();
    $result = $validator->hasAtLeastOneUploadedImage($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('image', $validator->getErrors());
    $this->assertSame('画像を1枚以上選択してください', $validator->getErrors()['image']);
  
    unset($_SESSION['tmp_image_path']);
  }

  public function testHasUploadImageWithUploadedFile():void
  {
    $request = [
      'image' => [
        'error' => [UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE, UPLOAD_ERR_OK]
      ]
    ];

    $validator = new ImageValidation();
    $result = $validator->hasUploadImage($request);
    $this->assertTrue($result);
  }
  public function testHasUploadImageWithNoFile():void
  {
    $request = [
      'image' => [
        'error' => [UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE, UPLOAD_ERR_NO_FILE]
      ]
    ];

    $validator = new ImageValidation();
    $result = $validator->hasUploadImage($request);
    $this->assertFalse($result);
  }

}