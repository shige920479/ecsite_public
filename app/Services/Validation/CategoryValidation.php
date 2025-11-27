<?php
namespace App\Services\Validation;

use App\Exceptions\ErrorHandler;
use App\Repositories\CategoryRepository;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\SubCategoryRepository;
use Exception;

class CategoryValidation extends BaseValidation
{
  public function __construct(
    private CategoryRepository $categoryRepo,
    private SubCategoryRepository $subCategoryRepo,
    private ItemCategoryRepository $itemCategoryRepo
  ){}

  public function validate(array $request)
  {
    try {
      if($this->validateName($request['name'])) {
        if($this->categoryRepo->isDuplicateName($request['name'])) {
          $this->errors['name'] = 'このカテゴリー名は登録済です';
        }
      }
      if($this->validateSlug($request['slug'])) {
        if($this->categoryRepo->isDuplicateSlug($request['slug'])) {
          $this->errors['slug'] = 'このスラグ名は登録済です';
        }
      }
    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }

    return empty($this->getErrors());
  }

  public function validateSub(array $request)
  {
    try {
      if($this->required('category_id', $request['category_id'])) {
        if(! $this->categoryRepo->existById($request['category_id'])) {
          $this->errors['category_id'] = 'カテゴリーが存在していません';
        }
      }
      if($this->validateName($request['name'])) {
        if($this->subCategoryRepo->isDuplicateName((int)$request['category_id'], $request['name'])) {
          $this->errors['name'] = 'このサブカテゴリー名は登録済です';
        }
      }
      $this->validateSlug($request['slug']);

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }

    return empty($this->getErrors());
  }

  public function validateItem(array $request, ?int $id)
  {
    if($this->required('sub_category_id', $request['sub_category_id'])) {
      try {
        if(! $this->subCategoryRepo->existById($request['sub_category_id'])) {
          $this->errors['sub_category_id'] = 'カテゴリーが存在していません';
        }
      } catch(Exception $e) {
        ErrorHandler::redirectWithCode(500);
      }
    }

    if($this->validateName($request['name'])) {
      try {
        if($this->itemCategoryRepo->isDuplicateName((int)$request['sub_category_id'], $request['name'], $id ?? null )) {
          $this->errors['name'] = 'この商品カテゴリー名は登録済です';
        }
      } catch(Exception $e) {
        ErrorHandler::redirectWithCode(500);
      }
    }

    $this->validateSlug($request['slug']);

    return empty($this->getErrors());
  }

  private function validateName(?string $name): bool
  {
    if($this->required('name', $name)) {
      if($this->maxLength('name', $name, 50)) {
        return true;
      }
    } 
    return false;
  }

  private function validateSlug(?string $slug): bool
  {
    if($this->required('slug', $slug)) {
      if($this->isAlphanumericAndWithinMaxLength('slug', $slug, 50)) {
        return true;
      }
    } 
    return false;
  }
}