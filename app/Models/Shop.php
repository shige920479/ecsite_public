<?php
namespace App\Models;

class Shop extends BaseModel
{
  public int $id;
  public int $owner_id;
  public string $name;
  public string $information;
  public string $filename;
  public int $is_selling;
  public string $created_at;
  public string $updated_at;
  public string|null $deleted_at;

}