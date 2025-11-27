<?php

use App\Repositories\ItemCategoryRepository;
use App\Repositories\ItemRepository;
use App\Services\Validation\ItemValidation;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertFalse;

class ItemValidationTest extends TestCase
{
  private ItemValidation $validator;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\ItemRepository */
  private ItemRepository $itemRepo; 
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\ItemCategoryRepository */
  private ItemCategoryRepository $itemCategoryRepo;

  protected function setUp(): void
  {
    $this->itemRepo = $this->createMock(ItemRepository::class);
    $this->itemCategoryRepo = $this->createMock(ItemCategoryRepository::class);

    $this->validator = new ItemValidation($this->itemRepo, $this->itemCategoryRepo);
  }

  public function testValidateStoreWithValidValues(): void
  {
    $request = [
      'shop_id' => 1,
      'item_category_id' => 12,
      'item_id' => 10,
      'name' => '欧風マグカップ',
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 1500,
      'sort_order' => 2,
      'is_selling' => 1,
    ];

    $this->itemRepo->expects($this->once())->method('isDuplicateName')->with(1, '欧風マグカップ', 10)
    ->willReturn(false);
    $this->itemCategoryRepo->expects($this->once())->method('existById')->with(12)
    ->willReturn(true);

    $result = $this->validator->validateStore($request, 'edit');

    $this->assertTrue($result);
  }

  // shop_idが存在しないケース
  public function testValidateStoreWithEmptyShopId(): void
  {
    $request = [
      // 'shop_id' => 1,
      'item_category_id' => 12,
      'item_id' => 10,
      'name' => '欧風マグカップ',
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 1500,
      'sort_order' => 2,
      'is_selling' => 1,
    ];

    $result = $this->validator->validateStore($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('shop_id', $this->validator->getErrors());
  }
  //item_category_id が存在しない、未指定
  public function testValidateStoreWithEmptyItemCategoryId(): void
  {
    $request = [
      'shop_id' => 1,
      // 'item_category_id' => 12,
      'item_id' => 10,
      'name' => '欧風マグカップ',
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 1500,
      'sort_order' => 2,
      'is_selling' => 1,
    ];

    $result = $this->validator->validateStore($request);
    $this->assertFalse($result);
    $this->assertArrayHasKey('item_category_id', $this->validator->getErrors());
  }
  // item_category_id が重複
  public function testValidateStoreWithDuplicateCategoryId(): void
  {
    $request = [
      'shop_id' => 1,
      'item_category_id' => 12,
      'item_id' => 10,
      'name' => '欧風マグカップ',
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 1500,
      'sort_order' => 2,
      'is_selling' => 1,
    ];

    $this->itemCategoryRepo->expects($this->once())->method('existById')->with(12)->willReturn(false);

    $result = $this->validator->validateStore($request);
    $this->assertFalse($result);
    $this->assertArrayHasKey('item_category_id', $this->validator->getErrors());
    $this->assertSame('未登録か使用できないカテゴリーです', $this->validator->getErrors()['item_category_id']);
  }
  // name が長すぎる
  public function testValidateStoreWithNameOverMax(): void
  {
    $request = [
      'shop_id' => 1,
      'item_category_id' => 12,
      'item_id' => 10,
      'name' => str_repeat('欧', 51),
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 1500,
      'sort_order' => 2,
      'is_selling' => 1,
    ];

    $this->itemCategoryRepo->expects($this->once())->method('existById')->with(12)->willReturn(true);
    $this->itemRepo->expects($this->never())->method('isDuplicateName');
    
    $result = $this->validator->validateStore($request);
    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->validator->getErrors());
  }
  // name 登録済み
  public function testValidateStoreWithDuplicateNameModeEdit(): void
  {
    $request = [
      'shop_id' => 1,
      'item_category_id' => 12,
      'item_id' => 10,
      'name' => '欧風マグカップ',
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 1500,
      'sort_order' => 2,
      'is_selling' => 1,
    ];

    $this->itemCategoryRepo->expects($this->once())->method('existById')->with(12)->willReturn(true);
    $this->itemRepo->expects($this->once())
      ->method('isDuplicateName')->with(1, '欧風マグカップ', 10)->willReturn(true);

    $result = $this->validator->validateStore($request, 'edit');
    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->validator->getErrors());
    $this->assertArrayHasKey('name', $this->validator->getOld());
    $this->assertSame('この商品名は登録済です', $this->validator->getErrors()['name']);
    $this->assertSame('欧風マグカップ', $this->validator->getOld()['name']);
  }

  public function testValidateStoreWithDuplicateNameModeCreate():void
  {
    $request = [
      'shop_id' => 1,
      'item_category_id' => 12,
      // 'item_id' => 10,
      'name' => '欧風マグカップ',
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 1500,
      'sort_order' => 2,
      'is_selling' => 1,
    ];
    
    $this->itemCategoryRepo->expects($this->once())->method('existById')->with(12)->willReturn(true);
    $this->itemRepo->expects($this->once())->method('isDuplicateName')->with(1,'欧風マグカップ', null)->willReturn(true);

    $result = $this->validator->validateStore($request);
    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->validator->getErrors());
    $this->assertArrayHasKey('name', $this->validator->getOld());
    $this->assertSame('この商品名は登録済です', $this->validator->getErrors()['name']);
    $this->assertSame('欧風マグカップ', $this->validator->getOld()['name']);
  }

  public function testValidateStoreWithUnsetInfoPriceSotIsSelling(): void
  {
    $request = [
      'shop_id' => 1,
      'item_category_id' => 12,
      // 'item_id' => 10,
      'name' => '欧風マグカップ',
      // 'information' => 'これは欧風マグカップの商品説明です',
      // 'price' => 1500,
      // 'sort_order' => 2,
      // 'is_selling' => 1,
    ];
    
    $this->itemCategoryRepo->expects($this->once())->method('existById')->with(12)->willReturn(true);
    $this->itemRepo->expects($this->once())->method('isDuplicateName')->with(1, '欧風マグカップ', null)->willReturn(false);

    $result = $this->validator->validateStore($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('information', $this->validator->getErrors());
    $this->assertArrayHasKey('price', $this->validator->getErrors());
    $this->assertArrayNotHasKey('sort_order', $this->validator->getErrors());
    $this->assertArrayHasKey('is_selling', $this->validator->getErrors());
  }

  public function testValidateStoreWithNotNumericPriceSort(): void
  {
    $request = [
      'shop_id' => 1,
      'item_category_id' => 12,
      // 'item_id' => 10,
      'name' => '欧風マグカップ',
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 'aaa',
      'sort_order' => 'c',
      'is_selling' => 1,
    ];

    $this->itemCategoryRepo->expects($this->once())->method('existById')->with(12)->willReturn(true);
    $this->itemRepo->expects($this->once())->method('isDuplicateName')->with(1, '欧風マグカップ', null)->willReturn(false);

    $result = $this->validator->validateStore($request);

    $this->assertFalse($result);
    $this->assertArrayHasKey('price', $this->validator->getErrors());
    $this->assertArrayHasKey('sort_order', $this->validator->getErrors());
  }

  public function testValidateStoreWithInvalidIsSelling(): void
  {
    $request = [
      'shop_id' => 1,
      'item_category_id' => 12,
      // 'item_id' => 10,
      'name' => '欧風マグカップ',
      'information' => 'これは欧風マグカップの商品説明です',
      'price' => 1200,
      'sort_order' => 2,
      'is_selling' => 3,
    ];

    $this->itemCategoryRepo->expects($this->once())->method('existById')->with(12)->willReturn(true);
    $this->itemRepo->expects($this->once())->method('isDuplicateName')->with(1, '欧風マグカップ', null)->willReturn(false);
    
    $result = $this->validator->validateStore($request);
    $this->assertFalse($result);
    $this->assertArrayHasKey('is_selling', $this->validator->getErrors());
  }

}