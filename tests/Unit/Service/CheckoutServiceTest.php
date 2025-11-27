<?php

require_once __DIR__ . '/../../../app/Exceptions/ErrorHandler.php';
require_once __DIR__ . '/../../../app/functions/common.php';
require_once __DIR__ . '/../TestTransactionHooksTrait.php';

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Repositories\CartRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\StockRepository;
use App\Services\Core\SessionService;
use App\Services\Helper\StripeService;
use App\Services\Helper\StripeSessionClient;
use App\Services\User\CheckoutService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\OAuth\InvalidRequestException;

use function PHPUnit\Framework\once;

class CheckoutServiceTest extends TestCase
{
  use TestTransactionHooksTrait;

  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\CartRepository */
  private CartRepository $cartRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\OrderRepository */
  private OrderRepository $orderRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\OrderItemRepository */
  private OrderItemRepository $orderItemRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\StockRepository */
  private StockRepository $stockRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Services\Helper\StripeSessionClient */
  private StripeSessionClient $stripeClient;
  private CheckoutService $checkService;

  protected function setUp(): void
  {
    $this->cartRepo = $this->createMock(CartRepository::class);
    $this->orderRepo = $this->createMock(OrderRepository::class);
    $this->orderItemRepo = $this->createMock(OrderItemRepository::class);
    $this->stockRepo = $this->createMock(StockRepository::class);
    $this->stripeClient = $this->createMock(StripeSessionClient::class);
    $this->checkService = new CheckoutService(
      $this->cartRepo, $this->orderRepo, $this->orderItemRepo, $this->stockRepo, $this->stripeClient);

    if(session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    $this->setUpTransactionHooks();
    file_put_contents(BASE_PATH . '/tests/tmp/test_log.txt', '');
  }

  protected function tearDown(): void
  {
    $this->resetTransactionHooks();

    if(! empty($_SESSION)) $_SESSION = [];
  }

  public static function setUpBeforeClass(): void
  {
    $dir = BASE_PATH . '/tests/tmp';
    if (!is_dir($dir)) {
      mkdir($dir, 0777, true);
    }

    ErrorHandler::$redirectCallback = function($code) {
      throw new \RuntimeException("redirect with code: {$code}");
    };

    ErrorHandler::$logCallback = function ($message) {
        file_put_contents(BASE_PATH . '/tests/tmp/test_log.txt', $message . "\n", FILE_APPEND);
    };

  }

  public function testGetOrderItemsAddCalcTotal正常系():void
  {
    $userId = 1;
    $cartIds = [1, 2];

    $this->cartRepo->expects($this->once())->method('getCartItemsByCartIds')
    ->with($userId, $cartIds)->willReturn([
      [
        'cart_id' => 1,
        'item_id' => 1,
        'item_name' => 'item1',
        'quantity' => 2,
        'price' => 1000,
      ],
      [
        'cart_id' => 2,
        'item_id' => 2,
        'item_name' => 'item2',
        'quantity' => 1,
        'price' => 1000,
      ],
    ]);

    $result = $this->checkService->getOrderItemsAddCalcTotal($userId, $cartIds);
    
    $this->assertIsArray($result);
    $this->assertSame([
      [
        'cart_id' => 1,
        'item_id' => 1,
        'item_name' => 'item1',
        'quantity' => 2,
        'price' => 1000,
        'amount' => 2200,
      ],
      [
        'cart_id' => 2,
        'item_id' => 2,
        'item_name' => 'item2',
        'quantity' => 1,
        'price' => 1000,
        'amount' => 1100
      ],
    ], $result);
  }

  public function testGetOrderItemsAddCalcTotal空のカート_ログ確認():void
  {
    $userId = 1;
    $cartIds = [];

    $this->cartRepo->expects($this->once())->method('getCartItemsByCartIds')
    ->with($userId, $cartIds)->willReturn([]);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('redirect with code: 403');

    $result = $this->checkService->getOrderItemsAddCalcTotal($userId, $cartIds);
  }

  public function testValidateBeforeOrder正常系():void
  {
    $userId = 1;
    $cartIds = [3, 4, 5];

    $this->cartRepo->expects($this->once())->method('getUserIdByCartIds')
      ->with([3, 4, 5])->willReturn([['user_id' => 1], ['user_id' => 1],['user_id' => 1]]);

    $this->cartRepo->expects($this->once())->method('getStockByCartIds')
      ->with([3, 4, 5])->willReturn([
        ['item_id' => 1, 'stock' => 10, 'quantity' => 3],
        ['item_id' => 2, 'stock' => 10, 'quantity' => 2],
        ['item_id' => 3, 'stock' => 10, 'quantity' => 1]
      ]);
    
    $this->cartRepo->expects($this->once())->method('checkIsSelling')
      ->with([3, 4, 5])->willReturn([
        [ 'item_id' => 1, 'is_selling' => 1],
        [ 'item_id' => 2, 'is_selling' => 1],
        [ 'item_id' => 3, 'is_selling' => 1]
      ]);
    $sessionMock = $this->createMock(SessionService::class);
    $sessionMock->expects($this->never())->method('set'); 

    $result = $this->checkService->validateBeforeOrder($userId, $cartIds);

    $this->assertTrue($result);
  }

  public function testValidateBeforeOrderカートが空でエラーmsg():void
  {
    $userId = 1;
    $cartIds = [];

    $result = $this->checkService->validateBeforeOrder($userId, $cartIds);

    $this->assertFalse($result);
    $this->assertArrayHasKey('errors', $_SESSION);
    $this->assertSame('商品が選択されておりません', $_SESSION['errors']['orders']);
  }

  #[DataProvider('providerCartUserIds')]
  public function testValidateBeforeOrderカート所有者が異なる(array $raw):void
  {
    $userId = 1;
    $cartIds = [2, 3];

    $this->cartRepo->expects($this->once())->method('getUserIdByCartIds')
      ->with($cartIds)->willReturn($raw);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('redirect with code: 403');
    
    $result = $this->checkService->validateBeforeOrder($userId, $cartIds);
  }

  public function testValidateBeforeOrder在庫不足は対象item_id戻す(): void
  {
    $userId = 1;
    $cartIds = [1, 2, 3];

    $this->cartRepo->expects($this->once())->method('getUserIdByCartIds')
    ->with($cartIds)->willReturn([
      ['user_id' => 1],
      ['user_id' => 1],
      ['user_id' => 1],
    ]);
    $this->cartRepo->expects($this->once())->method('getStockByCartIds')
    ->with($cartIds)->willReturn([
      ['item_id' => 5, 'stock' => 3 ,'quantity' => 2],
      ['item_id' => 6, 'stock' => 3 ,'quantity' => 3],
      ['item_id' => 7, 'stock' => 3 ,'quantity' => 4],
    ]);

    $result = $this->checkService->validateBeforeOrder($userId, $cartIds);

    $this->assertFalse($result);
    $this->assertSame('在庫が不足してます', $_SESSION['errors']['item_7']);
    $this->assertCount(1, $_SESSION['errors']);
  }

  public function testValidateBeforeOrder販売停止中_itemidを戻す():void
  {
    $userId = 1;
    $cartIds = [1, 2, 3];
    $this->cartRepo->expects($this->once())->method('getUserIdByCartIds')
    ->with($cartIds)->willReturn([
      ['user_id' => 1],
      ['user_id' => 1],
      ['user_id' => 1],
    ]);
    $this->cartRepo->expects($this->once())->method('getStockByCartIds')
    ->with($cartIds)->willReturn([
      ['item_id' => 5, 'stock' => 3 ,'quantity' => 1],
      ['item_id' => 6, 'stock' => 3 ,'quantity' => 1],
      ['item_id' => 7, 'stock' => 3 ,'quantity' => 1],
    ]);
    $this->cartRepo->expects($this->once())->method('checkIsSelling')
    ->with($cartIds)->willReturn([
      ['item_id' => 5, 'is_selling' => 1],
      ['item_id' => 6, 'is_selling' => 0],
      ['item_id' => 7, 'is_selling' => 0],
    ]);

    $result = $this->checkService->validateBeforeOrder($userId, $cartIds);

    $this->assertFalse($result);
    $this->assertSame('販売停止しています', $_SESSION['errors']['item_6']);
    $this->assertCount(1, $_SESSION['errors']);
  }
  
  public function testValidateBeforeOrder在庫不足_販売停止中_itemidを戻す():void
  {
    $userId = 1;
    $cartIds = [1, 2, 3];
    $this->cartRepo->expects($this->once())->method('getUserIdByCartIds')
    ->with($cartIds)->willReturn([
      ['user_id' => 1],
      ['user_id' => 1],
      ['user_id' => 1],
    ]);
    $this->cartRepo->expects($this->once())->method('getStockByCartIds')
    ->with($cartIds)->willReturn([
      ['item_id' => 5, 'stock' => 3 ,'quantity' => 1],
      ['item_id' => 6, 'stock' => 3 ,'quantity' => 1],
      ['item_id' => 7, 'stock' => 3 ,'quantity' => 4],
    ]);
    $this->cartRepo->expects($this->once())->method('checkIsSelling')
    ->with($cartIds)->willReturn([
      ['item_id' => 5, 'is_selling' => 1],
      ['item_id' => 6, 'is_selling' => 0],
      ['item_id' => 7, 'is_selling' => 0],
    ]);

    $result = $this->checkService->validateBeforeOrder($userId, $cartIds);

    $this->assertFalse($result);
    $this->assertSame('在庫が不足してます', $_SESSION['errors']['item_7']);
    $this->assertSame('販売停止しています', $_SESSION['errors']['item_6']);
    $this->assertCount(2, $_SESSION['errors']);
  }

  public function testCreateOrder正常系(): void
  {
    $userId = 1;
    $cartIds = [1, 2];

    $this->cartRepo->expects($this->once())->method('getPriceAndQuantity')
      ->with($cartIds)->willReturn([
        ['cart_id' => 1, 'item_id' => 2, 'price' => 1000, 'quantity' => 2],
        [ 'cart_id' => 1, 'item_id' => 3, 'price' => 2000, 'quantity' => 3]
      ]);
    
    $this->orderRepo->expects($this->once())->method('insertOrder')
      ->with($userId, 8800)->willReturn(10);
    
    $this->cartRepo->expects($this->once())->method('getCartItemsByCartIds')
     ->with($userId, $cartIds)->willReturn([
      ['price' => 1000, 'quantity' => 2],
      ['price' => 2000, 'quantity' => 3]
     ]);
    
    $this->orderItemRepo->expects($this->once())->method('insert')
     ->willReturn(true);
    
    $this->stockRepo->expects($this->once())->method('multiInsert')
     ->willReturn(true);

    $result = $this->checkService->createOrder($userId, $cartIds);

    $this->assertSame(10, $result);
    $log = file_get_contents(BASE_PATH. '/tests/tmp/test_log.txt');
    $this->assertStringContainsString("begin", $log);
    $this->assertStringContainsString("commit", $log);
    $this->assertStringNotContainsString("rollback", $log);
  }

  public function testCreateOrder異常系_order登録NG_rollback(): void
  {
    $userId = 1;
    $cartIds = [1, 2];

    $this->cartRepo->expects($this->once())->method('getPriceAndQuantity')
      ->with($cartIds)->willReturn([
        ['cart_id' => 1, 'item_id' => 2, 'price' => 1000, 'quantity' => 2],
        [ 'cart_id' => 1, 'item_id' => 3, 'price' => 2000, 'quantity' => 3]
      ]);
    
    $this->orderRepo->expects($this->once())->method('insertOrder')
      ->willThrowException(new \Exception('DBエラー: insertOrder失敗'));

    $result = $this->checkService->createOrder($userId, $cartIds);
    
    $this->assertNull($result);
    $log = file_get_contents(BASE_PATH . '/tests/tmp/test_log.txt');
    $this->assertStringContainsString('begin', $log);
    $this->assertStringNotContainsString('commit', $log);
    $this->assertStringContainsString('rollback', $log);
  }

  public function testCreateOrder異常系_order_item登録NG_rollback(): void
  {
    $userId = 1;
    $cartIds = [1, 2];

    $this->cartRepo->expects($this->once())->method('getPriceAndQuantity')
      ->with($cartIds)->willReturn([
        ['cart_id' => 1, 'item_id' => 2, 'price' => 1000, 'quantity' => 2],
        [ 'cart_id' => 1, 'item_id' => 3, 'price' => 2000, 'quantity' => 3]
      ]);
    
    $this->orderRepo->expects($this->once())->method('insertOrder')
      ->willReturn(10);
    
    $this->cartRepo->expects($this->once())->method('getCartItemsByCartIds')
      ->willReturn([
        ['price' => 1000, 'quantity' => 2],
        ['price' => 2000, 'quantity' => 3]
      ]);
    
    $this->orderItemRepo->expects($this->once())->method('insert')
      ->willThrowException(new \Exception('DBエラー: insert失敗'));
    
    $result = $this->checkService->createOrder($userId, $cartIds);

    $this->assertNull($result);
    $log = file_get_contents(BASE_PATH . '/tests/tmp/test_log.txt');
    $this->assertStringContainsString('begin', $log);
    $this->assertStringNotContainsString('commit', $log);
    $this->assertStringContainsString('rollback', $log);
  }

    public function testCreateOrder異常系_在庫multi登録NG_rollback(): void
  {
    $userId = 1;
    $cartIds = [1, 2];

    $this->cartRepo->expects($this->once())->method('getPriceAndQuantity')
      ->with($cartIds)->willReturn([
        ['cart_id' => 1, 'item_id' => 2, 'price' => 1000, 'quantity' => 2],
        [ 'cart_id' => 1, 'item_id' => 3, 'price' => 2000, 'quantity' => 3]
      ]);
    
    $this->orderRepo->expects($this->once())->method('insertOrder')
      ->willReturn(10);
    
    $this->cartRepo->expects($this->once())->method('getCartItemsByCartIds')
      ->willReturn([
        ['price' => 1000, 'quantity' => 2],
        ['price' => 2000, 'quantity' => 3]
      ]);
    
    $this->orderItemRepo->expects($this->once())->method('insert')
      ->willReturn(true);

    $this->stockRepo->expects($this->once())->method('multiInsert')
      ->willThrowException(new \Exception('DBエラー: multiInsert失敗'));
    
    $result = $this->checkService->createOrder($userId, $cartIds);

    $this->assertNull($result);
    $log = file_get_contents(BASE_PATH . '/tests/tmp/test_log.txt');
    $this->assertStringContainsString('begin', $log);
    $this->assertStringNotContainsString('commit', $log);
    $this->assertStringContainsString('rollback', $log);
  }

  public function testCreateStripeSession正常系(): void
  {
    $orderId = 1;
    $this->orderItemRepo->expects($this->once())->method('getOrderItemsByOrderId')
      ->with(1)->willReturn([[
          'item_id' => 1,
          'item_name' => 'item1',
          'quantity' => 2,
          'price' => 1000,
          'subtotal' => 2000
      ]]);
      
    $this->stripeClient->expects($this->once())->method('createCheckoutSession')
      ->willReturn('https://dummy-stripe-url.test');

    $result = $this->checkService->createStripeSession($orderId);

    $this->assertSame('https://dummy-stripe-url.test', $result);
  }

  public function testCreateStripeSession異常系(): void
  {
    $orderId = 1;
    $this->orderItemRepo->expects($this->once())->method('getOrderItemsByOrderId')
      ->with(1)->willReturn([[
          'item_id' => 1,
          'item_name' => 'item1',
          'quantity' => 2,
          'price' => 1000,
          'subtotal' => 2000
      ]]);
    $this->stripeClient->expects($this->once())->method('createCheckoutSession')
      ->willThrowException(new InvalidRequestException('Stripe-error'));

    $result = $this->checkService->createStripeSession($orderId);

    $this->assertNull($result);
    $log = file_get_contents(BASE_PATH . '/tests/tmp/test_log.txt');
    $this->assertStringContainsString('Stripe-error', $log);

  }

  public function testGetOrderId正常系(): void
  {
    $_GET['session_id'] = 'session123';
    $this->stripeClient->expects($this->once())->method('retrieve')
      ->with('session123')->willReturn((object)[
        'metadata' => (object)[
          'order_id' => 'order_999'
          ]
    ]);

    $result = $this->checkService->getOrderId();

    $this->assertSame('order_999', $result);
  }

  public function testGetOrderId異常系(): void
  {
    $_GET['session_id'] = 'session123';
    
    $this->stripeClient->expects($this->once())->method('retrieve')
      ->willThrowException(new Exception('stripe-error'));

    $result = $this->checkService->getOrderId();
    
    $this->assertNull($result);
    $log = file_get_contents(BASE_PATH . '/tests/tmp/test_log.txt');
    $this->assertStringContainsString('stripe-error', $log);
  }

  public function testDeleteOrderAndRestoreStock正常系(): void
  {
    $orderId = 777;
    $items = [[
      'item_id' => 11,
      'item_name' => 'test11',
      'quantity' => 2,
      'price' => 1200,
      'subtotal' => 2400
    ]];
    
    $this->orderItemRepo->expects($this->once())->method('getOrderItemsByOrderId')
      ->willReturn($items);
    
    $this->orderItemRepo->expects($this->once())
      ->method('deleteByOrderId')->with($orderId);

    $this->orderRepo->expects($this->once())
      ->method('deleteById')->with($orderId);
    
    $this->stockRepo->expects($this->once())
      ->method('multiInsert')->with($items, ADD_STOCK);

    $this->checkService->deleteOrderAndRestoreStock($orderId);

    $this->assertTrue(true);
    $log = file_get_contents(BASE_PATH . '/tests/tmp/test_log.txt');
    $this->assertStringContainsString('begin', $log);
    $this->assertStringContainsString('commit', $log);
    $this->assertStringNotContainsString('rollback', $log);  
  }

  public function testDeleteOrderAndRestoreStock異常系(): void
  {
    $orderId = 777;
    $items = [[
      'item_id' => 11,
      'item_name' => 'test11',
      'quantity' => 2,
      'price' => 1200,
      'subtotal' => 2400
    ]];

    $this->orderItemRepo->expects($this->once())->method('getOrderItemsByOrderId')
      ->willReturn($items);

    $this->orderItemRepo->expects($this->once())->method('deleteByOrderId')
      ->with($orderId)->willThrowException(new Exception('db-error'));

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('オーダー情報の削除に失敗しました。恐れ入りますが問い合わせ窓口へご連絡願います');
    $this->checkService->deleteOrderAndRestoreStock($orderId);

    $log = file_get_contents(BASE_PATH . '/tests/tmp/test_log.txt');
    $this->assertStringContainsString('begin', $log);
    $this->assertStringContainsString('rollback', $log);
    $this->assertStringNotContainsString('commit', $log);

  }

  public static function providerCartUserIds(): array
  {
    return [
        'count error' => [[[['user_id' => 5]]]],
        'not unique' => [[[['user_id' => 1], ['user_id' => 2]]]],
        'invalid user' => [[[['user_id' => 2], ['user_id' => 2]]]],
    ];
  }


}