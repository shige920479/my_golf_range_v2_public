<?php

use App\Database\DbConnect;
use App\Exceptions\BadRequestException;
use App\Exceptions\ServerErrorException;
use App\Repositories\UserRepository;
use App\Repositories\TemporaryUserRepository;
use App\Services\Auth\UserRegisterService;
use App\Services\Security\PasswordService;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserRegisterServiceTest extends TestCase
{
  /** */
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\TemporaryUserRepository */
  private TemporaryUserRepository $tempRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Repositories\UserRepository */
  private UserRepository $userRepo;
  /** @var \PHPUnit\Framework\MockObject\MockObject&\App\Database\DbConnect */
  private DbConnect $db;
  private UserRegisterService $service;
  
  protected function setUp(): void
  {
    $this->tempRepo = $this->createMock(TemporaryUserRepository::class);
    $this->userRepo =  $this->createMock(UserRepository::class);
    $this->db = $this->createMock(DbConnect::class);

    $this->service = new UserRegisterService(
      $this->tempRepo, $this->userRepo, new PasswordService() ,$this->db
    );
  }

  protected function tearDown(): void
  {
    $_SESSION = [];
  }

  #[Test]
  public function storeTemporaryUser_returns_token(): void
  {
    $req = ['email' => 'email@mail.com'];
    $this->tempRepo->expects($this->once())->method('store');

    $result = $this->service->storeTemporaryUser($req);

    $this->assertSame(32, strlen($result));
  }
  
  #[Test]
  public function verifyToken_returns_token(): void
  {
    $urlToken = bin2hex(random_bytes(16));
    $hashed = hash('sha256', $urlToken);
    $this->tempRepo->expects($this->once())->method('verify')
      ->with($hashed)->willReturn(['email' => 'token@mail.com']);

    $result = $this->service->verifyToken($urlToken);

    $this->assertSame('token@mail.com', $result);
  }

  #[Test]
  public function verifyToken_with_expired_token(): void
  {
    $urlToken = bin2hex(random_bytes(16));
    $hashed = hash('sha256', $urlToken);
    $this->tempRepo->expects($this->once())->method('verify')
      ->with($hashed)->willReturn(false);

    $result = $this->service->verifyToken($urlToken);

    $this->assertNull($result);
  }
  
  #[Test]
  public function verifyToken_with_invalid_token(): void
  {
    $urlToken = 'token123112312132';
    
    $this->expectException(BadRequestException::class);
    $result = $this->service->verifyToken($urlToken);
  }

  #[Test]
  public function registerUser_can_store_and_delete(): void
  {
    $inputs = [
      'password' => 'password123',
      'email' => 'test@mail.com'
    ];
    $_SESSION['url_token'] = 'urltoken12312312123';
    $hashedToken = hash('sha256', $_SESSION['url_token']);

    $this->userRepo->expects($this->once())
      ->method('store')
      ->with($this->callback(function($params) {
        return isset($params['password'])
          && password_verify('password123', $params['password']);
      }));
    $this->tempRepo->expects($this->once())
        ->method('delete')
        ->with(
            $this->equalTo($hashedToken),
            $this->equalTo($inputs['email'])
        );
    $this->db->expects($this->once())->method('beginTransaction');
    $this->db->expects($this->once())->method('commit');
    $this->db->expects($this->never())->method('rollBack');

    $this->service->registerUser($inputs);

    $_SESSION = [];
  }

  #[Test]
  public function registerUser_rollback_for_exception_(): void
  {
    $inputs = [
      'password' => 'password123',
      'email' => 'test@mail.com'
    ];
    $_SESSION['url_token'] = 'urltoken12312312123';
    $this->userRepo->expects($this->once())->method('store');
    $this->tempRepo->expects($this->once())->method('delete')->willThrowException(new Exception('delete-error'));
    $this->db->expects($this->once())->method('beginTransaction');
    $this->db->expects($this->never())->method('commit');
    $this->db->expects($this->once())->method('rollBack');

    $this->expectException(ServerErrorException::class);
    $this->service->registerUser($inputs);

    $_SESSION = [];
  }

}