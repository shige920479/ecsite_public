<?php
namespace App\Services\Owner;

use App\Database\DbConnect;
use App\Exceptions\ErrorHandler;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\ItemImageRepository;
use App\Repositories\ItemRepository;
use App\Repositories\ShopRepository;
use App\Repositories\StockRepository;
use App\Services\Core\SessionService;
use App\Services\Helper\ImageHandler;
use Exception;

class ItemService
{
  private ItemRepository $itemRepo;
  private ShopRepository $shopRepo;
  private ItemCategoryRepository $CategoryRepo;
  private ItemImageRepository $imageRepo;
  private StockRepository $stockRepo;
  private ImageHandler $imageHandler;

  public function __construct()
  {
    $this->itemRepo = new ItemRepository();
    $this->shopRepo = new ShopRepository();
    $this->CategoryRepo = new ItemCategoryRepository();
    $this->imageRepo = new ItemImageRepository();
    $this->stockRepo = new StockRepository();
    $this->imageHandler = new ImageHandler();

  }

  public function isValidOwnerShop(int $shopId): bool
  {
    try {
      $shop = $this->shopRepo->getByOwnerId(SessionService::get('owner.id'));
      return $shop->id === (int)$shopId;  
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
      return false;
    }
  }

  public function getConfirmViewData(): array
  {
    $previewData = SessionService::get('item_preview');
    try {
      $previewData['shop_name'] = $this->shopRepo->getNameById($previewData['shop_id']);
      $previewData['item_category_name'] = $this->CategoryRepo->getNameById($previewData['item_category_id']);
      $previewData['is_selling'] = $previewData['is_selling'] ? '販売中' : '停止中';
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }

    return $previewData;
  }

  public function storeItem(): int
  {
    try {
      $previewData = SessionService::get('item_preview');
      
      return $this->itemRepo->insert($previewData);
    
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
      return false; 
    }
  }

  public function updateItem(int $ownerId): int
  {
    try {
      $previewData = SessionService::get('item_preview');
      $previewData['item_id'] = SessionService::get('item_id');

      return $this->itemRepo->update($previewData, $ownerId);
    
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
      return false; 
    }
  }

  public function deleteItemAndImageAndStock(int $itemId): bool
  {
    try {
      DbConnect::beginTransaction();
 
      $uploadFiles = $this->imageRepo->getImagesByItemId($itemId);
      $this->imageRepo->deleteByItemId($itemId);
      
      $this->stockRepo->deleteByItemId($itemId);

      $this->itemRepo->softDelete($itemId);
      
      DbConnect::commitTransaction();

      foreach($uploadFiles as $image) {
          $this->imageHandler->deleteUploadedImage($image['filename'], 'item-images');
      }

      return true;

    } catch(Exception $e) {
      DbConnect::rollbackTransaction();
      SessionService::set('errors.item', '商品削除に失敗しました、再度実行願います');
      return false;
    }
  }
  
  public function paginate(array $request, int|array|null $categoryIds): array
  {
    $page = $request['page'] ?? 1;
    if(isset($request['per_page']) && in_array((int)$request['per_page'], PER_PAGE_OPTION, true)) {
      $perPage = (int)$request['per_page'];
    } else {
      $perPage = 8;
    }

    if(is_array($categoryIds)) {
      list($queryCategory, $categoryParam) = $this->queryCategoryAndParam($categoryIds);
    } else {
      $queryCategory = $this->queryCategoryAndParam($categoryIds);
      $categoryParam = $categoryIds;
    }

    $querySelect = ! empty($request['item_select']) ? $this->querySelect($request['item_select']) : '';
    $querySearch = ! empty($request['item_search']) ? " AND it.name LIKE :keyword" : '';
    $keyword = ! empty($request['item_search']) ? '%'. trim($request['item_search']) . '%' : '';

    try {
      $paginate = $this->itemRepo->getPagenateData($page, $querySearch, $keyword, $queryCategory, $categoryParam, $querySelect, $perPage);
      $paginate['item_select'] = $request['item_select'] ?? '';
      return $paginate;
    
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
      return [];
    }
  }

  public function getItemDetail(int $itemId): array
  {
    try {
      if(! $this->itemRepo->existById($itemId)) {
        ErrorHandler::log("不正な商品id:{$itemId}の入力)");
        ErrorHandler::redirectWithCode(404);
      }
      return $this->itemRepo->getItemAndShopAndCategoryAndStockById($itemId);
    
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
      return [];
    }
  }

  private function querySelect(?string $select): string
  {
    if(! empty($select)) {
      $query = match($select) {
        'price_asc' => " ORDER BY it.price ASC",
        'price_desc' => " ORDER BY it.price DESC",
        'date_desc' => " ORDER BY it.updated_at DESC",
        'shop_asc' => " ORDER BY sh.name ASC",
        default => " ORDER BY it.updated_at DESC"
      };
    } else {
      $query = ' ORDER BY it.updated_at DESC';
    }
    return $query;
  }

  private function queryCategoryAndParam(int|array|null $categoryIds): string|array
  {
    if($categoryIds !== null && is_array($categoryIds)) {
      $inPlaceholder = [];
      $param = [];

      foreach($categoryIds as $index => $id) {
        $key = ":item_category_{$index}";
        $inPlaceholder[] = $key;
        $param[$key] = $id;
      }
      $queryCategory = " AND it.item_category_id IN (" . implode(',', $inPlaceholder) . ")";
      
      return [$queryCategory, $param];

    } elseif($categoryIds !== null) {
      $queryCategory = " AND it.item_category_id = :item_category_id";
    } else {
      $queryCategory = '';
    }

    return $queryCategory;
  }
}