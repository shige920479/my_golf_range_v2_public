<?php
namespace App\Services\Auth;

use App\Database\DbConnect;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerErrorException;
use App\Repositories\TemporaryUserRepository;
use App\Repositories\UserRepository;
use App\Services\Security\PasswordService;

class UserRegisterService
{
  public function __construct(
    private TemporaryUserRepository $tempRepo,
    private UserRepository $userRepo,
    private PasswordService $password,
    private DbConnect $db
  )
  {
  }

  public function storeTemporaryUser(array $request): string
  {
    $token = bin2hex(random_bytes(16));
    $hashed = hash('sha256', $token);
    $this->tempRepo->store($request['email'], $hashed);

    return $token;
  }

  public function verifyToken(string $urlToken): ?string
  {
    if (!ctype_xdigit($urlToken) || strlen($urlToken) !== 32) {
      throw new BadRequestException("url_token 不整合: {$urlToken}");
    }
    $hash = hash('sha256', $urlToken);
    $row = $this->tempRepo->verify($hash);

    return $row['email'] ?? null;
  }

  public function registerUser(array $inputs): void
  {
    $hashed = $this->password->hashPassword($inputs['password']);
    $inputs['password'] = $hashed;
    $hashedUrlToken = hash('sha256', $_SESSION['url_token']);

    try {
      $this->db->beginTransaction();

      $this->userRepo->store($inputs);
      $this->tempRepo->delete($hashedUrlToken, $inputs['email']);
      
      $this->db->commit();

    } catch(\Throwable $e) {
      $this->db->rollBack();
      throw new ServerErrorException("ユーザー本登録でエラー:UserRegisterService::registerUser");
    }
  }

}