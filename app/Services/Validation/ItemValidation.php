<?php
namespace App\Services\Validation;

use App\Exceptions\ErrorHandler;
use App\Repositories\ItemCategoryRepository;
use App\Repositories\ItemRepository;
use Exception;

class ItemValidation extends BaseValidation
{
  public function __construct(
    private ItemRepository $itemRepo,
    private ItemCategoryRepository $itemCategoryRepo
  ){}

  public function validateStore(array $request, string $mode = 'create')
  {
    $itemId = match($mode) {'create' => null, 'edit' => $request['item_id']};

    try {
      if(! $this->required('shop_id', $request['shop_id'] ?? null)) {
        return false;
      }

      if($this->required('item_category_id', $request['item_category_id'] ?? null)) {
        if(! $this->itemCategoryRepo->existById($request['item_category_id'])) {
          $this->errors['item_category_id'] = '未登録か使用できないカテゴリーです';
        }
      }

      if($this->required('name', $request['name'] ?? null)) {
        if($this->maxLength('name', $request['name'], 50)) {
          if($this->itemRepo->isDuplicateName($request['shop_id'], $request['name'], $itemId)) {
            $this->errors['name'] = 'この商品名は登録済です';
            $this->old['name'] = $request['name'];
          }
        }
      }

      $this->required('information', $request['information'] ?? null);

      if($this->required('price', $request['price'] ?? null)) {
        $this->numeric('price', $request['price']);
      }

      if(! empty($request['sort_order'])) {
        $this->numeric('sort_order', $request['sort_order']);
      }

      if($this->required('is_selling', $request['is_selling'] ?? null)) {
        $this->validBoolean('is_selling', $request['is_selling']);
      }

    } catch(Exception $e) {
      ErrorHandler::redirectWithCode(500);
    }

    return empty($this->getErrors());
  }
}