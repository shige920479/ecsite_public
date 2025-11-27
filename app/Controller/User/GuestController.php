<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Repositories\FavoriteRepository;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\ItemImageRepository;
use App\Repositories\ItemRepository;
use App\Repositories\ShopRepository;
use App\Services\Admin\CategoryService;
use App\Services\Owner\ItemService;

class GuestController extends BaseController
{
  private ItemRepository $itemRepo;
  private ShopRepository $shopRepo;
  private ItemImageRepository $imageRepo;
  private ItemService $itemService;
  private CategoryService $categoryService;
  private ItemCategoryRepository $itemCategoryRepo;
  private FavoriteRepository $favoriteRepo;

  public function __construct()
  {
    parent::__construct();
    $this->itemRepo = new ItemRepository();
    $this->shopRepo = new ShopRepository();
    $this->imageRepo = new ItemImageRepository();
    $this->itemService = new ItemService();
    $this->categoryService = new CategoryService();
    $this->itemCategoryRepo = new ItemCategoryRepository();
    $this->favoriteRepo = new FavoriteRepository();
  }

  public function index(?string $parent = null, ?string $sub = null, ?string $item = null): void
  {
    $categoryIds = $this->categoryService->resolveCategoryId($parent, $sub, $item);
    $paginate = $this->itemService->paginate($this->request, $categoryIds);
    $categoryTree = $this->categoryService->buildCategoryTree($this->itemCategoryRepo->getCategoryGroup());
    $item_search = $this->request['item_search'] ?? '';
    $categoryPath = $this->categoryService->createCategoryPath($parent, $sub, $item);
    $backUrl = $_SERVER['REQUEST_URI'];

    $this->render(APP_PATH . '/Views/user/home.php', [
      'items' => $paginate['items'],
      'total' => $paginate['total'],
      'currentPage' => $paginate['current_page'],
      'totalPages' => $paginate['total_pages'],
      'perPage' => $paginate['per_page'],
      'item_select' => $paginate['item_select'],
      'item_search' => $item_search,
      'categoryTree' => $categoryTree,
      'parent' => $parent,
      'sub' => $sub,
      'item' => $item,
      'categoryPath' => $categoryPath,
      'backUrl' => $backUrl
    ]);
  }

  public function show(int $itemId): void
  {
    $item = $this->itemService->getItemDetail($itemId);
    $currentUrl = str_replace(PATH, '', $_SERVER['REQUEST_URI']);
    $loginForBackUrl = mb_substr($currentUrl, 0, mb_strpos($currentUrl, '?'));
    $isLoggedIn = isset($_SESSION['user']) ? "true" : "false";

    $images = $this->imageRepo->getImagesByItemId($itemId);

    $isFavorite = "false";
    if(isset($_SESSION['user'])) {
      if($this->favoriteRepo->isFavorited($_SESSION['user']['id'], $itemId)) {
        $isFavorite = "true";
      }
    }

    $this->render(APP_PATH . '/Views/user/show.php', [
      'item' => $item,
      'images' => $images,
      'backUrl' => $this->request['backUrl'] ?? null,
      'loginForBackUrl' => $loginForBackUrl,
      'isLoggedIn' => $isLoggedIn,
      'isFavorite' => $isFavorite
    ]);
  }
}