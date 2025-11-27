<?php
namespace App\Services\User;

use App\Exceptions\ErrorHandler;
use App\Repositories\CartRepository;
use App\Repositories\ItemRepository;
use App\Repositories\StockRepository;
use App\Services\Core\SessionService;
use App\Utils\RequestHelper;
use Exception;
use Throwable;

class CartService
{
  private ?int $lastStatus = null;
  private ?string $lastMessage = null;

  public function __construct(
    private CartRepository $cartRepo,
    private ItemRepository $itemRepo, 
    private StockRepository $stockRepo
  ){}

  public function getLastStatus(): ?int { return $this->lastStatus;}
  public function getLastMessage(): ?string { return $this->lastMessage;}

  private function fail(int $status, ?string $message = null): bool
  {
    $this->lastStatus = $status;
    $this->lastMessage = $message;
    return false;
  }

  private function ok(): bool
  {
    $this->lastStatus = null;
    $this->lastMessage = null;
    return true;
  }

  public function validateBeforeInsert(int $userId, int $itemId, int $quantity): bool
  {
      if(! $this->itemRepo->existById($itemId)) {
        ErrorHandler::log("存在しない商品id :{$itemId}");
        return $this->fail(404, '商品が見つかりません');
      }

      if($this->cartRepo->existByItemId($userId, $itemId)) {
        return $this->fail(409, '既にカート内に同じ商品があります');
      }

      $currentStock = $this->currentStock($itemId);
      if(! $this->hasEnoughStock($currentStock, $quantity)) {
        SessionService::set('old.quantity', $quantity);
        return $this->fail(409, '在庫数量を超えています');
      }

      return $this->ok();
  }

  public function updateQuantityWithValidation(int $cartId, $quantity)
  {
    if($cartId < 1 || $quantity < 1) {
      return $this->fail(400);
    }

    try {
      $cartItem = $this->cartRepo->getCartItemById($cartId);
      if (!$cartItem) {
        return $this->fail(404, 'カート情報が見つかりません');
      }

      $itemId = (int)$cartItem['item_id'];
      $currentStock = $this->currentStock($itemId);
      if(! $this->hasEnoughStock($currentStock, $quantity)) {
        return $this->fail(409, "在庫数量({$currentStock}個)を超えています");
      }

      $this->cartRepo->updateQuantity($cartId, $quantity);
      return $this->ok();

    } catch(Throwable $e) {
      return $this->fail(500, 'サーバーエラー');
    }
  }

  protected function currentStock(int $itemId): int
  {
    $row = $this->stockRepo->getCurrentStock($itemId);
    return (int)($row['current_stock'] ?? 0);
  }

  protected function hasEnoughStock(int $currentStock, int $required): bool
  {
    return $required <= $currentStock;
  }
}