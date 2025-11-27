<?php
namespace App\Models;

class ItemImage extends BaseModel
{
  public int $id;
  public int $item_id;
  public string $filaname;
  public ?int $sort_order;
  public string $created_at;
  public string $updated_at;
  public string $deleted_at;

}