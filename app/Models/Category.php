<?php
namespace App\Models;

use App\Repositories\CategoryRepository;

class Category extends BaseModel
{
  public int $id;
  public string $name;
  public string $slug;
  public string $created_at;

}