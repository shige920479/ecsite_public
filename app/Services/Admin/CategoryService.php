<?php
namespace App\Services\Admin;

use App\Exceptions\ErrorHandler;
use App\Repositories\CategoryRepository;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\SubCategoryRepository;
use Exception;

class CategoryService
{
  private CategoryRepository $categoryRepo;
  private SubCategoryRepository $subCategoryRepo;
  private ItemCategoryRepository $itemCategoryRepo;

  public function __construct()
  {
    $this->categoryRepo = new CategoryRepository;
    $this->subCategoryRepo = new SubCategoryRepository;
    $this->itemCategoryRepo = new ItemCategoryRepository;
  }

  public function insertCategory(array $request)
  {
    try {
      return $this->categoryRepo->insert($request);

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function insertSubCategory(array $request)
  {
    try {
      return $this->subCategoryRepo->insert($request);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }
  public function insertItemCategory(array $request)
  {
    try {
      return $this->itemCategoryRepo->insert($request);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function updateItemCategory(array $request, int $id)
  {
    try {
      return $this->itemCategoryRepo->update($request, $id);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function getCategory() {
    try {
      return $this->categoryRepo->getAll();
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }
  
  public function getSubCategory() {
    try {
      return $this->subCategoryRepo->getAll();
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function getItemCategoryByID(int $id)
  {
    try {
      return $this->itemCategoryRepo->getById($id);
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }
  }

  public function getSubCategoryWithCategory(): array
  {
    try {
      $categoryGroup = $this->subCategoryRepo->getCategoryGroup();
      $grouped = [];
      foreach($categoryGroup as $row) {
        $grouped[$row['category']][] = [
          'id' => $row['id'],
          'name' => $row['name']
        ];
      }
      return $grouped;

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
      return [];
    }
  }
  
  /**
   * 全カテゴリー情報の取得・再配列
   * 
   */
  public function getItemCategoryGroup()
  {
    $categoryGroup = $this->itemCategoryRepo->getCategoryGroup();
    // return $categoryGroup;
    $grouped = [];
    
    foreach($categoryGroup as $row) {
      $category = $row['category_name'];
      $subCategory = $row['sub_category_name'];
      $grouped[$category][$subCategory][] = [
        'id' => $row['item_category_id'],
        'name' => $row['item_category_name'],
        'slug' => $row['item_category_slug']
      ];
    }
    return $grouped;
  }

  public function buildCategoryTree(array $rows): array
  {
      $tree = [];
      $index = []; // 親カテゴリ slug → 親配列への参照
      $subIndex = []; // 親slug|subslug → サブカテゴリ配列への参照

      foreach ($rows as $row) {
          $catSlug = $row['category_slug'];
          $catName = $row['category_name'];
          $subSlug = $row['sub_category_slug'];
          $subName = $row['sub_category_name'];
          $itemId = $row['item_category_id'];
          $itemSlug = $row['item_category_slug'];
          $itemName = $row['item_category_name'];

          // 親カテゴリが未登録なら追加
          if (! isset($index[$catSlug])) {
              $tree[] = [
                  'slug' => $catSlug,
                  'name' => $catName,
                  'children' => [],
              ];
              $index[$catSlug] = &$tree[array_key_last($tree)];
          }

          // サブカテゴリの複合キー
          $subIndexKey = $catSlug . '|' . $subSlug;

          if (! isset($subIndex[$subIndexKey])) {
              $index[$catSlug]['children'][] = [
                  'slug' => $subSlug,
                  'name' => $subName,
                  'children' => [],
              ];
              $subIndex[$subIndexKey] = &$index[$catSlug]['children'][array_key_last($index[$catSlug]['children'])];
          }

          // 商品カテゴリ追加
          $subIndex[$subIndexKey]['children'][] = [
              'id' => $itemId,
              'slug' => $itemSlug,
              'name' => $itemName,
          ];
      }

      return $tree;
  }
  /**
   * SlugからカテゴリIDを特定する処理
   */
  public function resolveCategoryId(?string $parent, $sub, $item): int|array|null
  {
    try {
      if($item) {
        return $this->itemCategoryRepo->getIdBySlug($parent, $sub, $item);
      } elseif($sub) {
        return $this->subCategoryRepo->getItemIdBySlug($parent, $sub);
      } elseif($parent) {
        return $this->categoryRepo->getItemIdBySlug($parent);
      }
      return null;
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
      return null;
    }
  }
  
  /**
   * ビューに渡すパスの生成
   */
  public function createCategoryPath(?string $parent, $sub, $item): ?string
  {
    if($parent) $categoryPath = 'category/' . $parent;
    if($sub) $categoryPath = $categoryPath . '/' . $sub;
    if($item) $categoryPath = $categoryPath . '/' . $item;
    if(empty($categoryPath)) $categoryPath = null;

    return $categoryPath;
  }

}