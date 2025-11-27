<?php
namespace App\Models;

class Stock extends BaseModel
{
  public int $id;
  public int $item_id;
  public int $stock_diff;
  public ?string $reason;
  public string $created_at;
}