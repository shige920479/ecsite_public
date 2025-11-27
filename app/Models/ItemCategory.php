<?php
namespace App\Models;

class ItemCategory extends BaseModel
{
  public int $id;
  public int $sub_category_id;
  public string $name;
  public string $slug;
  public string $created_at;
}