<?php

use App\Database\DbConnect;
use App\Repositories\UserRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
  private DbConnect $db;
  private UserRepository $userRepo;

  protected function setUp(): void
  {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $this->db = new DbConnect($pdo);
    $this->userRepo = new UserRepository($this->db);

    $sql = "CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            firstname VARCHAR(30) NOT NULL,
            lastname  VARCHAR(30) NOT NULL,
            firstnamekana  VARCHAR(100) NOT NULL,
            lastnamekana VARCHAR(100) NOT NULL,
            email VARCHAR(50) NOT NULL UNIQUE,
            phone VARCHAR(255) NOT NULL,
            gender VARCHAR(10),
            password VARCHAR(255) NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
    
    $this->db->execute($sql);
  }

  #[Test]
  public function store_can_store(): void
  {
    $inputs = $this->provideUser();
    $inputs['gender'] = null;

    $result = $this->userRepo->store($inputs);
    $this->assertSame(1, $result);

    $id = $this->db->lastInsertId();
    $raw = $this->db->fetch("SELECT * FROM users WHERE id = {$id}");
    $this->assertSame($inputs['firstname'], $raw['firstname']);
    $this->assertSame($inputs['email'], $raw['email']);
    $this->assertEquals(1, $raw['status']);
    $this->assertNull($raw['gender']);
  }

  #[Test]
  public function existsByEmail_with_exists_and_not_exists(): void
  {
    $inputs = $this->provideUser();

    $result = $this->userRepo->store($inputs);

    $exists = 'taro.yamada@mail.com';
    $result = $this->userRepo->existsByEmail($exists);
    $this->assertSame(1, $result);

    $exists = 'jiro.suzuki@mail.com';
    $result = $this->userRepo->existsByEmail($exists);
    $this->assertSame(0, $result);
  }
  #[Test]
  public function store_with_fails_on_duplicate_email(): void
  {
    $inputs = $this->provideUser();

    $this->userRepo->store($inputs);
    $this->expectException(PDOException::class);
    $this->userRepo->store($inputs);
  }

  #[Test]
  public function store_fails_on_missing_required_field(): void
  {
    $inputs = $this->provideUser();
    $inputs['firstname'] = null;
  
    $this->expectException(PDOException::class);
    $this->userRepo->store($inputs);
  }

  #[Test]
  public function findByEmail_can_get_user_by_email(): void
  {
    $inputs = $this->provideUser();
    $this->userRepo->store($inputs);

    $email = 'taro.yamada@mail.com';
    $result = $this->userRepo->findByEmail($email);

    $this->assertSame('太郎', $result['firstname']);
    $this->assertSame('123-456-7890', $result['phone']);
    $this->assertSame('password123', $result['password']);
  }
  #[Test]
  public function findByEmail_not_exists_email_returns_false(): void
  {
    $inputs = $this->provideUser();
    $this->userRepo->store($inputs);

    $email = 'not.exists@mail.com';
    $result = $this->userRepo->findByEmail($email);

    $this->assertFalse($result);
  }

  private function provideUser(): array
  {
    $inputs = [
      'firstname' => '太郎',
      'lastname' => '山田',
      'firstnamekana' => 'たろう',
      'lastnamekana' => 'やまだ',
      'email' => 'taro.yamada@mail.com',
      'phone' => '123-456-7890',
      'gender' => 'mail',
      'password' => 'password123'
    ];

    return $inputs;
  }

}
