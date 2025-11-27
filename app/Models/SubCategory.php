<?php
namespace App\Models;

class SubCategory extends BaseModel
{
  public int $id;
  public int $category_id;
  public string $name;
  public string $slug;
  public string $created_at;
}