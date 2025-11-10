<?php
namespace App\Services\Security;

class TokenManager
{
  public function generateToken(): string
  {
    return bin2hex(random_bytes(32));
  }

  public function get(string $key = 'token'): string
  {
    if (! isset($_SESSION[$key])) {
      $_SESSION[$key] = $this->generateToken();
    }
    return $_SESSION[$key];
  }

  public function destroy(string $key = 'token'): void
  {
    unset($_SESSION[$key]);
  }
}