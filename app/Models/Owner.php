<?php
namespace App\Models;

use App\Contracts\Authenticatable;
use App\Services\Security\PasswordService;

class Owner extends BaseModel implements Authenticatable
{
  public int $id;
  public string $name;
  public string $email;
  public string $password;
  public string $created_at;
  public string $updated_at;
  public string|null $deleted_at;

  public function getId(): int { return $this->id; }
  public function getName(): string { return $this->name; }
  public function getEmail(): string { return $this->email; }

  public static function createForRegister($data)
  {
    $data['password'] = PasswordService::hashPassword($data['password']);
    return new self($data);
  }

}