<?php
namespace App\Services\Owner;

use App\Exceptions\ErrorHandler;
use App\Repositories\StockRepository;
use App\Services\Core\SessionService;
use Exception;

class StockService
{
  private StockRepository $stockRepo;

  public function __construct()
  {
    $this->stockRepo = new StockRepository();
  }

  public function storeStock(int $itemId, array $request)
  {
    if($request['up_down'] === 'reduce') {
      $request['stock_diff'] = $request['stock_diff'] * -1; 
    }

    $stockData = $this->stockRepo->getCurrentStock($itemId);
    if(($stockData['current_stock'] + $request['stock_diff']) < 0) {
      SessionService::set('errors.stock_diff', '在庫数が0を下回っています');
      return false;
    };

    return $this->stockRepo->insert($itemId, $request);
  }
}