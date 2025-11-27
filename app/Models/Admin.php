<?php
namespace App\Models;

use App\Contracts\Authenticatable;

class Admin extends BaseModel implements Authenticatable
{
  public int $id;
  public string $name;
  public string $email;
  public string $password;
  public string $created_at;
  public string $updated_at;

  public function getId(): int { return $this->id; }
  public function getName(): string { return $this->name; }
  public function getEmail(): string { return $this->email; }

}