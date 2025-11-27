<?php
namespace App\Services\Core;

use App\Contracts\Authenticatable;
use App\Models\Admin;
use App\Models\Owner;
use App\Models\User;

class SessionService
{
  public static function set(string $key, $value): void
  {
    $keys = explode('.', $key);
    $ref = &$_SESSION;

    foreach($keys as $segment) {
      if(! isset($ref[$segment]) || ! is_array($ref[$segment])) {
        $ref[$segment] = [];
      }
      $ref = &$ref[$segment];
    }

    $ref = $value;
  }

  public static function get(string $key, $default = null)
  {
    $keys = explode('.' , $key);
    $ref = $_SESSION;

    foreach($keys as $segment) {
      if(! isset($ref[$segment])) {
        return $default;
      }
      $ref = $ref[$segment];
    }

    return $ref;
  }

  public static function forget(string $key): void
  {
    $key = explode('.', $key);
    $ref = &$_SESSION;

    foreach($key as $i => $segment) {
      if(! isset($ref[$segment])) {
        return;
      }

      if($i === count($key) - 1) {
        unset($ref[$segment]);
        return;
      }

      $ref = &$ref[$segment];
    }    
  }

  public static function flash(string $key, $default = null)
  {
    $value = self::get($key, $default);
    self::forget($key);
    return $value;
  }

  public static function loginSessionGenerate(Authenticatable $user, string $role): void
  {
    session_regenerate_id(true);
    self::set("{$role}.id", $user->id);
    self::set("{$role}.name", $user->name);
    self::set("{$role}.email", $user->email);
  }

  public static function clear(string|array $keys):void
  {
    foreach((array)$keys as $key) {
      unset($_SESSION[$key]);
    }
  }

}