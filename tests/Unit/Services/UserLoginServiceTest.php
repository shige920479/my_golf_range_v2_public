<?php

use App\Repositories\OwnerRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\UserLoginService;
use App\Services\Security\PasswordService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserLoginServiceTest extends TestCase
{
  /** @var MockObject&UserRepository */
  private UserRepository $userRepo;
  /** @var MockObject&OwnerRepository */
  private OwnerRepository $ownerRepo;
  /** @var MockObject&PasswordService */
  private PasswordService $passwordService;
  private UserLoginService $service;

  protected function setUp(): void
  {
    $this->userRepo = $this->createMock(UserRepository::class);
    $this->ownerRepo = $this->createMock(OwnerRepository::class);
    $this->passwordService = $this->createMock(PasswordService::class);
    $this->service = new UserLoginService($this->userRepo, $this->ownerRepo, $this->passwordService);
  }

  #[Test]
  public function find_with_valid_email_returns_true(): void
  {
    $this->userRepo->method('existsByEmail')->willReturn(1);

    $result = $this->service->find('true@mail.com');
    $this->assertTrue($result);
  }
  #[Test]
  public function find_with_invalid_email_returns_false(): void
  {
    $this->userRepo->method('existsByEmail')->willReturn(0);

    $result = $this->service->find('false@mail.com');
    $this->assertFalse($result);
  }
  #[Test]
  public function verify_with_valid_values():void
  {
    $user = [
      'email' => 'true@mail.com',
      'password' => 'password123'
    ];

    $this->userRepo->expects($this->once())->method('findByEmail')
      ->with('true@mail.com')->willReturn($user);
    $this->passwordService->method('verifyPassword')->willReturn(true);

    $result = $this->service->verify($user['email'], $user['password']);

    $this->assertEquals($user, $result);
  }
  #[Test]
  public function verify_with_not_existing_user_returns_false():void
  {
    $user = [
      'email' => 'true@mail.com',
      'password' => 'password123'
    ];
    $this->userRepo->expects($this->once())->method('findByEmail')->willReturn(false);

    $result = $this->service->verify($user['email'], $user['password']);

    $this->assertFalse($result);
  }
  #[Test]
  public function verify_with_wrong_password_returns_false():void
  {
    $user = [
      'email' => 'true@mail.com',
      'password' => 'password123'
    ];
    $passwordService = new PasswordService();
    $hashed = $passwordService->hashPassword($user['password']);
    
    $this->userRepo->expects($this->once())->method('findByEmail')
      ->with('true@mail.com')->willReturn([
        'email' => $user['email'],
        'password' => 'invalid_password'
      ]);

    $result = $this->service->verify($user['email'], $user['password']);

    $this->assertFalse($result);
  }

}
