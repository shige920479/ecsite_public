<?php

use App\Exceptions\ErrorHandler;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Services\User\StripeWebhookService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StripeWebhookServiceTest extends TestCase
{
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\OrderRepository */
  private OrderRepository $orderRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\CartRepository */
  private CartRepository $cartRepo;
  private StripeWebhookService $webhookService;

  protected function setUp(): void
  {
    $this->orderRepo = $this->createMock(OrderRepository::class);
    $this->cartRepo = $this->createMock(CartRepository::class);
    $this->webhookService = new StripeWebhookService($this->orderRepo, $this->cartRepo);

    file_put_contents(BASE_PATH . '/tests/tmp/test_log.txt', '');
  }

  public static function setUpBeforeClass(): void
  {
    $dir = BASE_PATH . '/tests/tmp';
    if(! is_dir($dir)) mkdir($dir, 0777, true);

    ErrorHandler::$logCallback = function ($message) use($dir) {
      file_put_contents($dir . '/test_log.txt', $message . PHP_EOL, FILE_APPEND);
    };
  }

  #[Test]
  public function registerSessionStripeId_正常系(): void
  {
    $orderId = 1;
    $sessionId = 'ses123';

    $this->orderRepo->expects($this->once())->method('findById')
      ->with($orderId)->willReturn([
        'id' => $orderId,
        'user_id' => 10,
        'stripe_session_id' => null,
      ]);

    $this->orderRepo->expects($this->once())->method('registerSessionId')
      ->with($orderId, $sessionId)->willReturn(true);

    $result = $this->webhookService->registerStripeSessionId($orderId, $sessionId);

    $this->assertNull($result);
  }

  #[Test]
  public function registerSessionStripeId_登録済でreturn(): void
  {
    $orderId = 1;
    $sessionId = 'ses123';

    $this->orderRepo->expects($this->once())->method('findById')
      ->with($orderId)->willReturn([
        'id' => 1,
        'user_id' => 10,
        'stripe_session_id' => 'stripe123',
      ]);
    
    $this->orderRepo->expects($this->never())->method('registerSessionId');

    $result = $this->webhookService->registerStripeSessionId($orderId, $sessionId);
    
    $this->assertNull($result);
  }

  #[Test]
  public function clearOrderedItemsFromCart_正常系(): void
  {
    $orderId = 1;
    $this->orderRepo->expects($this->once())->method('findById')
      ->with($orderId)->willReturn([
          'id' => $orderId,
          'user_id' => 10,
          'stripe_session_id' => 'stripe123',
        ]);
    $this->cartRepo->expects($this->once())->method('deleteByUserIdAndOrderId')
      ->with(10, $orderId)->willReturn(true);
    
    $result = $this->webhookService->clearOrderedItemsFromCart($orderId);

    $this->assertNull($result);
  }

  #[Test]
  public function clearOrderedItemsFromCart_存在しないオーダー(): void
  {
    $orderId = 1;
    $this->orderRepo->expects($this->once())->method('findById')
      ->with($orderId)->willReturn(false);
    
    $this->webhookService->clearOrderedItemsFromCart($orderId);

    $log = file_get_contents(BASE_PATH . '/tests/tmp/test_log.txt');
    $this->assertStringContainsString("不正なWebhookアクセス: 存在しないorder_id={$orderId}", $log);
  }




  




}