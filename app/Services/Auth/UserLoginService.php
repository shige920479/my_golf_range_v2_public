<?php
namespace App\Services\Auth;

use App\Repositories\OwnerRepository;
use App\Repositories\UserRepository;
use App\Services\Security\PasswordService;

class UserLoginService
{

  public function __construct(
    private UserRepository $userRepo,
    private OwnerRepository $ownerRepo,
    private PasswordService $passwordService
  )
  {
  }

  /** アカウント登録確認 */
  public function find(string $email, ?string $role = null): bool
  {
    if($role === 'owner') {
      return $this->ownerRepo->existsByEmail($email) > 0 ? true : false;
    }
    return $this->userRepo->existsByEmail($email) > 0 ? true : false;
  }

  /** パスワード確認 */
  public function verify(string $email, string $password, ?string $role = null): array|false
  {
    if($role === 'owner') {
      $loginUser = $this->ownerRepo->findByEmail($email);
    } else {
      $loginUser = $this->userRepo->findByEmail($email);
    }
    if (! $loginUser) {
      return false;
    }
    $verified = $this->passwordService->verifyPassword($password, $loginUser['password']);
    return $verified ? $loginUser : false;
  }
}