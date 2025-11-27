<?php
namespace App\Models;

class Item extends BaseModel
{
  public int $id;
  public int $shop_id;
  public int $item_category_id;
  public string $name;
  public string $information;
  public string $price;
  public ?int $sort_order;
  public int $is_selling;
  public ?string $created_at;
  public ?string $updated_at;
  public ?string $deleted_at;
}