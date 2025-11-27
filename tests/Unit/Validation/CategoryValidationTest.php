<?php

use App\Exceptions\ErrorHandler;
use App\Repositories\CategoryRepository;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\SubCategoryRepository;
use PHPUnit\Framework\TestCase;
use App\Services\Validation\CategoryValidation;

class CategoryValidationTest extends TestCase
{
  private CategoryValidation $validator;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\CategoryRepository */
  private CategoryRepository $categoryRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\SubCategoryRepository */
  private SubCategoryRepository $subCategoryRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\ItemCategoryRepository */
  private ItemCategoryRepository $itemCategoryRepo;

  protected function setUp(): void
  {
    $this->categoryRepo = $this->createMock(CategoryRepository::class);
    $this->subCategoryRepo = $this->createMock(SubCategoryRepository::class);
    $this->itemCategoryRepo = $this->createMock(ItemCategoryRepository::class);

    $this->validator = new CategoryValidation(
      $this->categoryRepo,
      $this->subCategoryRepo,
      $this->itemCategoryRepo
    );    
  }

  public function testValidateWithDuplicateName(): void
  {
    $request = [
      'name' => 'キッチン雑貨',
      'slug' => 'slug'
    ];
    $this->categoryRepo
      ->expects($this->once())
      ->method('isDuplicateName')
      ->with('キッチン雑貨')
      ->willReturn(true);

    $result = $this->validator->validate($request);

    $this->assertFalse($result);
    $this->assertSame('このカテゴリー名は登録済です', $this->validator->getErrors()['name']);
  }

  public function testValidateSubWithInvalidCategoryId():void
  {
    $request = [
      'category_id' => 99,
      'name' => '収納用品',
      'slug' => 'slug'
    ];

    $this->categoryRepo
    ->expects($this->once())
    ->method('existById')
    ->with(99)
    ->willReturn(false);

    $result = $this->validator->validateSub($request);

    $this->assertFalse($result);
    $this->assertSame('カテゴリーが存在していません', $this->validator->getErrors()['category_id']);
  }

  public function testValidateSubWithDuplicateName():void
  {
    $request = [
      'category_id' => 1,
      'name' => 'マグカップ',
      'slug' => 'slug'
    ];

    $this->categoryRepo
    ->method('existById')
    ->willReturn(true);

    $this->subCategoryRepo
      ->expects($this->once())
      ->method('isDuplicateName')
      ->with(1, 'マグカップ')
      ->willReturn(true);

    $result = $this->validator->validateSub($request);
    $this->assertFalse($result);
    $this->assertSame('このサブカテゴリー名は登録済です', $this->validator->getErrors()['name']);
  }

  public function testValidateSubWithValidValue():void
  {
    $request = [
      'category_id' => 1,
      'name' => 'カラー',
      'slug' => 'slug'
    ];
    
    $this->categoryRepo
      ->expects($this->once())
      ->method('existById')
      ->with(1)
      ->willReturn(true);

    $this->subCategoryRepo
      ->expects($this->once())
      ->method('isDuplicateName')
      ->with(1, 'カラー')
      ->willReturn(False);
    
    $result = $this->validator->validateSub($request);
    $this->assertTrue($result);
  }

  public function testValidateItemWithInvalidCategoryId(): void
  {
    $request = [
      'sub_category_id' => 999,
      'name' => 'サイズ',
      'slug' => 'slug'
    ];

    $this->subCategoryRepo
      ->expects($this->once())
      ->method('existById')
      ->with(999)
      ->willReturn(false);
    
    $result = $this->validator->validateItem($request, null);
    $this->assertFalse($result);
    $this->assertArrayHasKey('sub_category_id', $this->validator->getErrors());
    $this->assertSame('カテゴリーが存在していません', $this->validator->getErrors()['sub_category_id']);
  }

  public function testValidateItemWithDuplicateName():void
  {
    $request = [
      'sub_category_id' => 1,
      'name' => "北欧風",
      'slug' => 'slug'
    ];

    $this->subCategoryRepo
      ->expects($this->once())
      ->method('existById')
      ->with(1)
      ->willReturn(true);
    
    $this->itemCategoryRepo
      ->expects($this->once())
      ->method('isDuplicateName')
      ->with(1, '北欧風')
      ->willReturn(true);

    $result = $this->validator->validateItem($request, null);
    $this->assertFalse($result);
    $this->assertSame('この商品カテゴリー名は登録済です', $this->validator->getErrors()['name']);
  }

  public function testValidateItemWithValidValue():void
  {
    $request = [
      'sub_category_id' => 1,
      'name' => '北欧風',
      'slug' => 'slug'
    ];

    $this->subCategoryRepo
      ->method('existById')
      ->willReturn(true);

    $this->itemCategoryRepo
      ->method('isDuplicateName')
      ->willReturn(false);

    $result = $this->validator->validateItem($request, null);
    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
  }

}