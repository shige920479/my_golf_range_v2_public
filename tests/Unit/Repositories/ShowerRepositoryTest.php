<?php

use App\Database\DbConnect;
use App\Repositories\ShowerRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShowerRepositoryTest extends TestCase
{

  private ?DbConnect $db = null;
  private ?ShowerRepository $showerRepo = null;

  protected function setUp(): void
  {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $this->db = new DbConnect($pdo);
    $this->showerRepo = new ShowerRepository($this->db);

    $sql = "CREATE TABLE shower (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            mainte_date DATE,
            del_flag TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
    
    $this->db->execute($sql);
  }
  protected function tearDown(): void
  {
      $this->db = null;
      $this->showerRepo = null;
  }

  #[Test]
  public function get_can_fetch_all(): void
  {
    $this->seedShower();

    $res = $this->showerRepo->get();

    $this->assertCount(1, $res);
    $this->assertSame('2025-04-23', $res[0]['mainte_date']);
  }

  #[Test]
  public function get_empty_record(): void
  {
    $res = $this->showerRepo->get();

    $this->assertSame([], $res);
  }

  #[Test]
  public function isAvailableDate_with_not_mainte_date(): void
  {
    $this->seedShower();
    $date = '2025-04-22';
    $res = $this->showerRepo->isAvailableDate($date);

    $this->assertTrue($res);
  }

  #[Test]
  public function isAvailableDate_with_mainte_date(): void
  {
    $this->seedShower();

    $date = '2025-04-23';

    $res = $this->showerRepo->isAvailableDate($date);

    $this->assertFalse($res);
  }

  #[Test]
  private function seedShower(): void
  {
    $sql = "INSERT INTO shower (mainte_date, del_flag)
            VALUES
            ('2025-04-23', 0)";
    $this->db->execute($sql);
  }
}