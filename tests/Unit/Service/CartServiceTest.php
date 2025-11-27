<?php

use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ItemRepository;
use App\Repositories\StockRepository;
use App\Services\User\CartService;
use PhpParser\Builder\Method;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CartServiceTest extends TestCase
{
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\CartRepository */
  private CartRepository $cartRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&App\Repositories\ItemRepository */
  private ItemRepository $itemRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&App\Repositories\StockRepository */
  private StockRepository $stockRepo;
  private CartService $cartService;

  protected function setUp(): void
  {
    if(session_status() !== PHP_SESSION_ACTIVE) {
      @session_start();
    }
    $_SESSION = [];

    $this->cartRepo = $this->createMock(CartRepository::class);
    $this->itemRepo = $this->createMock(ItemRepository::class);
    $this->stockRepo = $this->createMock(StockRepository::class);
    
    $this->cartService = new CartService(
      $this->cartRepo, $this->itemRepo, $this->stockRepo
    );
  }

  protected function tearDown(): void
  {
    if(! empty($_SESSION['old.quantity'])) {
      unset($_SESSION['old.quantity']);
    }
  }
  // 成功系
  public function testValidateBeforeInsertWithValidValue():void
  {
    $userId = 1;
    $itemId = 2;
    $quantity = 3;

    $this->itemRepo->expects($this->once())->method('existById')
      ->with(2)->willReturn(true);
    $this->cartRepo->expects($this->once())->method('existByItemId')
      ->with(1, 2)->willReturn(false);
    $this->stubStock(2, 100);

    $result = $this->cartService->validateBeforeInsert($userId, $itemId, $quantity);

    $this->assertSame(true, $result);
    $this->assertNull($this->cartService->getLastStatus());
    $this->assertNull($this->cartService->getLastMessage());
    $this->assertArrayNotHasKey('old', $_SESSION);
  }

  /** 失敗系 */

  public function testValidationBeforeInsert商品未存在404を返す():void
  {
    $userId = 1;
    $itemId = 2;
    $quantity = 5;

    $this->itemRepo->expects($this->once())
      ->method('existById')->with(2)->willReturn(false);
    $this->cartRepo->expects($this->never())->method('existByItemId');
    $this->stockRepo->expects($this->never())->method('getCurrentStock');
    
    $result = $this->cartService->validateBeforeInsert($userId, $itemId, $quantity);

    $this->assertSame(false, $result);
    $this->assertSame(404, $this->cartService->getLastStatus());
    $this->assertSame('商品が見つかりません', $this->cartService->getLastMessage());
    $this->assertArrayNotHasKey('old', $_SESSION);
  }
  public function testValidateBeforeInsertカート重複409を返す():void
  {
    $userId = 1;
    $itemId = 2;
    $quantity = 5;

    $this->itemRepo->expects($this->once())->method('existById')
      ->with(2)->willReturn(true);
    $this->cartRepo->expects($this->once())->method('existByItemId')
      ->with(1,2)->willReturn(true);
    $this->stockRepo->expects($this->never())->method('getCurrentStock');
    
    $result = $this->cartService->validateBeforeInsert($userId, $itemId, $quantity);
    
    $this->assertSame(false, $result);
    $this->assertSame(409, $this->cartService->getLastStatus());
    $this->assertSame('既にカート内に同じ商品があります', $this->cartService->getLastMessage());
    $this->assertArrayNotHasKey('old', $_SESSION);
  }
  public function testValidateBeforeInsert在庫不足409を返す():void
  {
    $userId = 1;
    $itemId = 2;
    $quantity = 5;
    $this->itemRepo->expects($this->once())->method('existById')
      ->with(2)->willReturn(true);
    $this->cartRepo->expects($this->once())->method('existByItemId')
      ->with(1,2)->willReturn(false);
    $this->stubStock($itemId, 3);

    $result = $this->cartService->validateBeforeInsert($userId, $itemId, $quantity);

    $this->assertSame(false, $result);
    $this->assertSame(409, $this->cartService->getLastStatus());
    $this->assertSame('在庫数量を超えています', $this->cartService->getLastMessage());
    $this->assertArrayHasKey('old', $_SESSION);
    $this->assertArrayHasKey('quantity', $_SESSION['old']);
    $this->assertSame(5, $_SESSION['old']['quantity']);
  }

  #[DataProvider('provideStockAndQuantityPairs')]
  public function testValidateBeforeInser在庫と数量の境界線でも正常に処理(int $stock, int $quantity):void
  {
    $userId = 1;
    $itemId = 2;
    $quantity = $quantity;

    $this->itemRepo->expects($this->once())->method('existById')->willReturn(true);
    $this->cartRepo->expects($this->once())->method('existByItemId')->willReturn(false);
    $this->stubStock($itemId, $stock);

    $result = $this->cartService->validateBeforeInsert($userId, $itemId, $quantity);

    $this->assertSame(true, $result);

    $this->assertTrue($result);
    $this->assertNull($this->cartService->getLastStatus());
    $this->assertNull($this->cartService->getLastMessage());
    $this->assertArrayNotHasKey('old', $_SESSION); 
  }

  public function testUpdateQuantityWithValidation正常系():void
  {
    $cartId = 1;
    $quantity = 2;

    $this->cartRepo->expects($this->once())->method('getCartItemById')
      ->with(1)->willReturn(['item_id' => 1]);    
    $this->stockRepo->expects($this->once())->method('getCurrentStock')
      ->with(1)->willReturn(['current_stock' => 2]);
    $this->cartRepo->expects($this->once())->method('updateQuantity')
      ->with($cartId, $quantity)->willReturn(true);

    $result = $this->cartService->updateQuantityWithValidation($cartId, $quantity);

    $this->assertSame(true, $result);
    $this->assertNull($this->cartService->getLastStatus());
    $this->assertNull($this->cartService->getLastMessage());
  }

  public function testUpdateQuantityWithValidationカートid異常値と数量1未満(): void
  {
    $cartId = 0;
    $quantity = 0;

    $result = $this->cartService->updateQuantityWithValidation($cartId, $quantity);

    $this->assertSame(false, $result);
    $this->assertSame(400, $this->cartService->getLastStatus());
  }
  public function testUpdateQuantityWithValidationカート情報の不整合404(): void
  {
    $cartId = 100;
    $quantity = 1;

    $this->cartRepo->expects($this->once())->method('getCartItemById')
      ->with(100)->willReturn(false); // getCartItemById->fetch

    $result = $this->cartService->updateQuantityWithValidation($cartId, $quantity);

    $this->assertfalse($result);
    $this->assertSame(404, $this->cartService->getLastStatus());
    $this->assertSame('カート情報が見つかりません', $this->cartService->getLastMessage());
  }

  public function testUpdateQuantityWithValidation在庫不足409():void
  {
    $cartId = 1;
    $quantity = 5;

    $this->cartRepo->expects($this->once())->method('getCartItemById')
      ->with(1)->willReturn(['item_id' => 2]);

    $this->stockRepo->expects($this->once())->method('getCurrentStock')
      ->with(2)->willReturn(['current_stock' => 3]);
    
    $this->cartRepo->expects($this->never())->method('updateQuantity');
    
    $result = $this->cartService->updateQuantityWithValidation($cartId, $quantity);

    $this->assertFalse($result);
    $this->assertSame(409, $this->cartService->getLastStatus());
    $this->assertSame("在庫数量(3個)を超えています", $this->cartService->getLastMessage());
  }

  public function testUpdateQuantityWithValidation例外発生時は500を返す():void
  {
    $cartId = 1;
    $quantity = 2;

    $this->cartRepo->expects($this->once())->method('getCartItemById')
      ->with($cartId)->willThrowException(new \Exception('DB Error'));
    
    $result = $this->cartService->updateQuantityWithValidation($cartId, $quantity);

    $this->assertFalse($result);
    $this->assertSame(500, $this->cartService->getLastStatus());
    $this->assertSame('サーバーエラー', $this->cartService->getLastMessage());
  }

  public static function provideStockAndQuantityPairs(): array
  {
      return [
          'equal values' => [5, 5],
          'more stock'   => [10, 5],
          'just enough'  => [1, 1],
      ];
  }

  // helper: $this->subStock(商品id, CurrentStockを指定)
  private function stubStock(int $itemId, int $stock):void
  {
    $this->stockRepo->method('getCurrentStock')
      ->with($itemId)->willReturn(['current_stock' => $stock]);
  }
}