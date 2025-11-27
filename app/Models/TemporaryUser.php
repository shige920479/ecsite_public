<?php
namespace App\Models;

use Carbon\Carbon;

class TemporaryUser
{
  public string $email;
  public string $verification_code;
  public string $expires_at;

  public function __construct(array $data)
  {
    $this->email = $data['email'];
    $this->verification_code = $data['verification_code'];
    $this->expires_at = $data['expires_at'];
  }

  public function isExpired(): bool
  {
    return Carbon::parse($this->expires_at)->isPast();
  }
}