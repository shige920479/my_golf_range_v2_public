<?php
namespace App\Services\Security;

class PasswordService
{
  public function hashPassword(string $password): string
  {
    return password_hash($password, PASSWORD_BCRYPT); 
  }

  public function verifyPassword(string $password, $hash): bool
  {
    return password_verify($password, $hash);
  }

}