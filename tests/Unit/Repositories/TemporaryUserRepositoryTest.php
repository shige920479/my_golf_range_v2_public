<?php

use App\Database\DbConnect;
use App\Repositories\TemporaryUserRepository;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TemporaryUserRepositoryTest extends TestCase
{
  private DbConnect $db;
  private TemporaryUserRepository $tempRepo;

  protected function setUp(): void
  {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $this->db = new DbConnect($pdo);
    $this->tempRepo = new TemporaryUserRepository($this->db);

    $sql = "CREATE TABLE temp_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email VARCHAR(50) NOT NULL,
            url_token VARCHAR(255) NOT NULL,
            expired_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";

    $this->db->execute($sql);
  }

  #[Test]
  public function store_can_store(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';

    $result = $this->tempRepo->store($email, $urlToken);

    $this->assertSame(1, $result);

    $row = $this->db->fetch("SELECT * FROM temp_users");
    $this->assertSame($email, $row['email']);
    $this->assertSame($urlToken, $row['url_token']);
    $this->assertNotNull($row['expired_at']);
  }

  #[Test]
  public function isBeforeExpired_before_expired(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';
    $validTime= Carbon::now()->addMinutes(1)->format('Y-m-d H:i:s');

    $sql = "INSERT INTO temp_users (email, url_token, expired_at)
            VALUES (:email, :url_token, :expired_at)";
    $this->db->execute($sql, ['email' => $email, 'url_token' => $urlToken, 'expired_at' => $validTime]);

    $result = $this->tempRepo->isBeforeExpired($email);
    $this->assertSame(1, $result);
  }

  #[Test]
  public function isBeforeExpired_over_expired(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';
    $invalidTime= Carbon::now()->addMinutes(-1)->format('Y-m-d H:i:s');

    $sql = "INSERT INTO temp_users (email, url_token, expired_at)
            VALUES (:email, :url_token, :expired_at)";
    $this->db->execute($sql, ['email' => $email, 'url_token' => $urlToken, 'expired_at' => $invalidTime]);

    $result = $this->tempRepo->isBeforeExpired($email);
    
    $this->assertSame(0, $result);

  }


  #[Test]
  public function verify_can_verify_hashed_token_and_returns_email(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';
    $hashed = hash('sha256', $urlToken);
    $this->tempRepo->store($email, $hashed);

    $result = $this->tempRepo->verify($hashed);

    $this->assertSame('test@mail.com', $result['email']);
  }

  #[Test]
  public function verify_with_invalid_token_and_returns_false(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';
    $this->tempRepo->store($email, $urlToken);

    $hash = hash('sha256', 'token123');
    $result = $this->tempRepo->verify('token345');

    $this->assertfalse($result);
  }

  #[Test]
  public function getByEmail_can_get_email(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';
    $this->tempRepo->store($email, $urlToken);

    $result = $this->tempRepo->getByEmail($email);

    $this->assertSame($email, $result['email']);
    $this->assertSame($urlToken, $result['url_token']);
    $this->assertNotNull($result['expired_at']);
  }

  #[Test]
  public function getByEmail_can_not_email_is_expired(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';
    $beforeTime= Carbon::now()->addMinutes(-60)->format('Y-m-d H:i:s');

    $sql = "INSERT INTO temp_users (email, url_token, expired_at)
            VALUES (:email, :url_token, :expired_at)";
    $this->db->execute($sql, ['email' => $email, 'url_token' => $urlToken, 'expired_at' => $beforeTime]);

    $result = $this->tempRepo->getByEmail($email);

    $this->assertFalse($result);
  }

  #[Test]
  public function delete_can_delete(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';
    $this->tempRepo->store($email, $urlToken);

    $result = $this->tempRepo->delete($urlToken, $email);

    $this->assertSame(1, $result);
    
    $record = $this->db->fetchColumn(
      "SELECT COUNT(*) FROM temp_users WHERE email = :email AND url_token = :url_token",
      ['email' => $email, 'url_token' => $urlToken]
    );
    $this->assertSame("0", $record);
  }

  #[Test]
  public function delete_can_not_delete_with_invalid_email(): void
  {
    $email = 'test@mail.com';
    $urlToken = 'token123';
    $this->tempRepo->store($email, $urlToken);

    $result = $this->tempRepo->delete($urlToken, 'abcd@mail.com');

    $this->assertSame(0, $result);
    
    $record = $this->db->fetchColumn(
      "SELECT COUNT(*) FROM temp_users WHERE email = :email AND url_token = :url_token",
      ['email' => $email, 'url_token' => $urlToken]
    );
    $this->assertSame("1", $record);
  }
}