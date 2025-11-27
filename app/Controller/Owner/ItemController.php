<?php
namespace App\Controller\Owner;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Middleware\AuthMiddleware;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\ItemRepository;
use App\Repositories\ShopRepository;
use App\Services\Admin\CategoryService;
use App\Services\Core\SessionService;
use App\Services\Helper\ImageHandler;
use App\Services\Owner\ItemService;
use App\Services\Validation\ItemValidation;

class ItemController extends BaseController
{
  private ItemService $itemService;
  private CategoryService $categoryService;
  private ShopRepository $shopRepo;
  private ItemValidation $validator;
  private ItemRepository $itemRepo;
  private ItemCategoryRepository $itemCategoryRepo;

  public function __construct()
  {
    parent::__construct();
    $this->itemService = new ItemService();
    $this->categoryService = new CategoryService();
    $this->shopRepo = new ShopRepository();
    $this->itemRepo = new ItemRepository();
    $this->itemCategoryRepo = new ItemCategoryRepository();
    $this->validator = new ItemValidation($this->itemRepo, $this->itemCategoryRepo);
    AuthMiddleware::checkAuth('owner');
  }

  public function index()
  {
    if(isset($_SESSION['tmp_image_path'])) clearTmpImageSessionAndFile();
    $items = $this->itemRepo->getAllItemByOwner(SessionService::get('owner.id'));
    $this->render(APP_PATH . '/Views/owner/items/items-list.php', ['items' => $items]);
  }

  public function create()
  {
    if(isset($_SESSION['tmp_image_path'])) clearTmpImageSessionAndFile();
    SessionService::clear('item_preview');
    $shop = $this->shopRepo->getByOwnerId(SessionService::get('owner.id'));
    $categoryGroup = $this->categoryService->getItemCategoryGroup();

    $this->render(APP_PATH . '/Views/owner/items/create.php', [
      'shop' => $shop,
      'categoryGroup' => $categoryGroup,
    ]);
  }

  public function confirm()
  {
    $mode = $this->request['mode'] ?? null;
    if(! in_array($mode, ['create', 'edit'], true)) {
      ErrorHandler::redirectWithCode(400);
    }
    if(! $this->validator->validateStore($this->request, $mode)) {
      SessionService::set('errors', $this->validator->getErrors());
      SessionService::set('old', $this->validator->getOld());
      match ($mode) {
        'create' => redirect('/owner/item/create'),
        'edit' => redirect("/owner/item/{$this->request['item_id']}/edit")
      };
    }
    SessionService::set('old', $this->validator->getOld());
    
    if(! $this->itemService->isValidOwnerShop($this->request['shop_id'])) {
      ErrorHandler::redirectWithCode(403);
    }

    $storeData = only($this->request, [
      'shop_id', 'item_category_id', 'name', 'price', 'sort_order', 'information', 'is_selling'
    ]);
    if(empty($storeData['sort_order'])) $storeData['sort_order'] = null;
    SessionService::set('item_preview', $storeData);
    SessionService::set('mode', $mode);
    
    if($mode === 'edit') {
      AuthMiddleware::authorizeItemOwner($this->request['item_id']);
      SessionService::set('item_id', $this->request['item_id']);
    } 
    redirect('/owner/item/confirm');
  }

  public function confirmView()
  {
    $viewData = $this->itemService->getConfirmViewData();
    $this->render(APP_PATH . '/Views/owner/items/confirm.php', [
      'viewData' => $viewData,
      'mode' => SessionService::get('mode'),
      'item_id' => SessionService::get('item_id'),
      'is_confirm' => true
    ]);
  }

  public function store()
  {
    $previewData = SessionService::get('item_preview');
    if(! $this->itemService->isValidOwnerShop($previewData['shop_id'])) {
      ErrorHandler::redirectWithCode(403);
    }

    $itemId = $this->itemService->storeItem();
    if($itemId) {
      SessionService::forget('item_preview');
      SessionService::forget('old');
      SessionService::set('success', '商品を新規登録しました');
    }
    redirect("/owner/item/{$itemId}/image");
  }

  public function edit(int $itemId)
  {
    SessionService::clear('item_preview');
    $item = $this->itemRepo->findItemById($itemId);
    $shop = $this->shopRepo->getById($item->shop_id);
    if(SessionService::get('owner.id') !== $shop->owner_id) {
      ErrorHandler::redirectWithCode(403);
    }
    $categoryGroup = $this->categoryService->getItemCategoryGroup();

    $this->render(APP_PATH . '/Views/owner/items/edit.php', [
      'item' => $item,
      'shop' => $shop,
      'categoryGroup' => $categoryGroup,
    ]);
  }

  public function update()
  {
    $ownerId = $_SESSION['owner']['id'];
    $itemId = SessionService::get('item_id');
    if (! $itemId) ErrorHandler::redirectWithCode(400);
    AuthMiddleware::authorizeItemOwner($itemId);

    $updateCount = $this->itemService->updateItem($ownerId);
    if($updateCount === 0) {
      ErrorHandler::log("不正な商品情報のアップデート：item_id: {$itemId}, owner_id: {$ownerId}");
      SessionService::clear(['item_preview', 'mode', 'item_id']);
      ErrorHandler::redirectWithCode(403);
    }
    SessionService::clear(['item_preview', 'mode', 'item_id']);
    SessionService::set('success', '商品情報を更新しました');
    redirect('/owner/items');
  }

  public function delete(int $itemId): void
  {
    AuthMiddleware::authorizeItemOwner($itemId);

    if(! $this->itemRepo->existById($itemId)) {
      SessionService::set('errors.item', '商品が見つかりませんでした');
      redirect('/owner/items');
    }
    if(! $this->itemService->deleteItemAndImageAndStock($itemId)) {
      redirect("/owner/items");
    }
    SessionService::set('success', '商品・画像・在庫情報を削除しました');
    redirect("/owner/items");
  }
}