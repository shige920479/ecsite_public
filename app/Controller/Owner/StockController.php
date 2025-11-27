<?php
namespace App\Controller\Owner;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Middleware\AuthMiddleware;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\ItemImageRepository;
use App\Repositories\ItemRepository;
use App\Repositories\StockRepository;
use App\Services\Core\SessionService;
use App\Services\Owner\StockService;
use App\Services\Validation\StockValidation;
use Exception;

class StockController extends BaseController
{
  private StockService $stockService;
  private ItemRepository $itemRepo;
  private ItemImageRepository $imageRepo;
  private ItemCategoryRepository $categoryRepo;
  private StockRepository $stockRepo;
  private StockValidation $validator;

  public function __construct()
  {
    parent::__construct();
    $this->stockService = new StockService();
    $this->itemRepo = new ItemRepository();
    $this->imageRepo = new ItemImageRepository();
    $this->categoryRepo = new ItemCategoryRepository();
    $this->stockRepo = new StockRepository();
    $this->validator = new StockValidation();
    AuthMiddleware::checkAuth('owner');
  }

  public function create(int $itemId)
  {
    AuthMiddleware::authorizeItemOwner($itemId);

    $item = $this->itemRepo->findItemById($itemId);
    $category = $this->categoryRepo->getItemNameAndSubNameById($item->item_category_id);
    $itemImage = $this->imageRepo->getMainImageByItemId($itemId);
    $quantity = $this->stockRepo->getCurrentStock($itemId);

    $this->render(APP_PATH . '/Views/owner/stocks/create.php', [
      'itemId' => $itemId,
      'item' => $item,
      'category' => $category,
      'itemImage' => $itemImage,
      'currentStock' => $quantity['current_stock']
    ]);
  }

  public function store(int $itemId)
  {
    AuthMiddleware::authorizeItemOwner($itemId);

    if(! $this->validator->validate($this->request)) {
      SessionService::set('errors', $this->validator->getErrors());
      SessionService::set('old', $this->validator->getOld());
      redirect("/owner/item/{$itemId}/stock");
    }
    
    try {
      $reuslt = $this->stockService->storeStock($itemId, $this->request);
      if(! $reuslt) {
        redirect("/owner/item/{$itemId}/stock");
      } 
      SessionService::set('success', '在庫数量を登録しました');
      redirect("/owner/items");

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
    

  }
}