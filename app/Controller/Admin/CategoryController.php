<?php
namespace App\Controller\Admin;

use App\Controller\BaseController;
use App\Exceptions\ErrorHandler;
use App\Middleware\AuthMiddleware;
use App\Repositories\CategoryRepository;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\SubCategoryRepository;
use App\Services\Admin\CategoryService;
use App\Services\Core\SessionService;
use App\Services\Validation\CategoryValidation;

class CategoryController extends BaseController
{
  private CategoryService $categoryService;
  private CategoryValidation $validator;

  public function __construct()
  {
    parent::__construct();
    $this->categoryService = new CategoryService();

    $categoryRepo = new CategoryRepository();
    $subCategoryRepo = new SubCategoryRepository();
    $itemCategoryRepo = new ItemCategoryRepository();
    $this->validator = new CategoryValidation($categoryRepo, $subCategoryRepo, $itemCategoryRepo);
    AuthMiddleware::checkAuth('admin');
  }

  public function index()
  {
    $categoryGroup = $this->categoryService->getItemCategoryGroup();
    $this->render(APP_PATH . '/Views/admin/category-list.php', ['categoryGroup' => $categoryGroup]);
  }

  // Category
  public function createCategory()
  {
    $this->render(APP_PATH . '/Views/admin/category.php');
  }
  public function storeCategory()
  {
    if(! $this->validator->validate($this->request)) {
      SessionService::set('errors', $this->validator->getErrors());
      SessionService::set('old', $this->validator->getOld());
      redirect('/admin/category');
    }
    
    $this->categoryService->insertCategory($this->request);
    SessionService::set('success', '新規カテゴリーを登録しました');
    redirect('/admin/category');
  }

  // SubCategory
  public function createSubCategory()
  {
    $categories = $this->categoryService->getCategory();
    $this->render(APP_PATH . '/Views/admin/sub-category.php', ['categories' => $categories]);
  }

  public function storeSubCategory()
  {
    if(! $this->validator->validateSub($this->request)) {
      SessionService::set('errors', $this->validator->getErrors());
      SessionService::set('old', $this->validator->getOld());
      redirect('/admin/subCategory');
    }
    $this->categoryService->insertSubCategory($this->request);
    SessionService::set('success', '新規サブカテゴリーを登録しました');
    redirect('/admin/subCategory');
  }

  // ItemCategory
  public function createItemCategory(){
    $categoryGroup = $this->categoryService->getSubCategoryWithCategory();
    $this->render(APP_PATH . '/Views/admin/item-category.php', ['categoryGroup' => $categoryGroup]);
  }

  public function storeItemCategory(){
    if(! $this->validator->validateItem($this->request, null)) {
      SessionService::set('errors', $this->validator->getErrors());
      SessionService::set('old', $this->validator->getOld());
      redirect('/admin/itemCategory');
    }
    $this->categoryService->insertItemCategory($this->request);
    SessionService::set('success', '新規商品カテゴリーを登録しました');
    redirect('/admin/itemCategory');
  }

  public function editItemCategory(int $id)
  {
    $categoryGroup = $this->categoryService->getSubCategoryWithCategory();
    $itemCategory = $this->categoryService->getItemCategoryByID($id);
    if($itemCategory === null) ErrorHandler::redirectWithCode(404);

    $this->render(APP_PATH . '/Views/admin/item-category-edit.php', [
      'categoryGroup' => $categoryGroup,
      'itemCategory' => $itemCategory
    ]);
  }

  public function updateItemCategory(int $id){

    if(! $this->validator->validateItem($this->request, $id)) {
      SessionService::set('errors', $this->validator->getErrors());
      SessionService::set('old', $this->validator->getOld());
      redirect("/admin/itemCategory/{$id}/edit");
    }
    $this->categoryService->updateItemCategory($this->request, $id);
    SessionService::set('success', '商品カテゴリー名を更新しました');
    redirect('/admin/itemCategoryList');

  }

}