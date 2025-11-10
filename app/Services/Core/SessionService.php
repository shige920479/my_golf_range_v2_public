<?php
namespace App\Services\Core;

class SessionService
{
  public function __construct()
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
  }

  public function set(string $key, mixed $value): void
  {
    $keys = explode('.', $key);
    $ref = &$_SESSION;

    foreach($keys as $segment) {
      if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
        $ref[$segment] = [];
      }
      $ref = &$ref[$segment];
    }
    $ref = $value;
  }

  public function get(string $key, mixed $default = null): mixed
  {
    $keys = explode('.' , $key);
    $ref = $_SESSION;

    foreach($keys as $segment) {
      if (! isset($ref[$segment])) {
        return $default;
      }
      $ref = $ref[$segment];
    }
    return $ref;
  }

  public function forget(string $key): void
  {
    $key = explode('.', $key);
    $ref = &$_SESSION;

    foreach($key as $i => $segment) {
      if (! isset($ref[$segment])) {
        return;
      }

      if ($i === count($key) - 1) {
        unset($ref[$segment]);
        return;
      }

      $ref = &$ref[$segment];
    }    
  }

  public function forgetMulti(array $keys): void
  {
    foreach($keys as $key) {
      $this->forget($key);
    }
  }

  public function flash(string $key, mixed $default = null)
  {
    $value = self::get($key, $default);
    self::forget($key);
    return $value;
  }

  public function loginSessionGenerate(array $user, string $role): void
  {
    session_regenerate_id(true);
    self::set("{$role}.id", $user['id']);
    if ($role === 'user') {
      self::set("{$role}.name", $user['lastname'] . $user['firstname']);
    } else {
      self::set("{$role}.name", $user['name']);
    }
    self::set("{$role}.email", $user['email']);
  }

  public function logoutSession()
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    unset($_SESSION['admin'], $_SESSION['owner'], $_SESSION['user']);
    $_SESSION = [];

    if (ini_get("session_use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() -42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
      );
    }
  
    session_destroy();
  }
  

}