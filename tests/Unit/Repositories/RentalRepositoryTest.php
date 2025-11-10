<?php

use App\Database\DbConnect;
use App\Repositories\RentalRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RentalRepositoryTest extends TestCase
{
  private ?DbConnect $db = null;
  private ?RentalRepository $rentalRepo = null;

  protected function setUp(): void
  {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $this->db = new DbConnect($pdo);
    $this->rentalRepo = new RentalRepository($this->db);

    $sql = "CREATE TABLE rental (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            brand VARCHAR(50) NOT NULL,
            model VARCHAR(50) NOT NULL,
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
      $this->rentalRepo = null;
  }

  #[Test]
  public function store_can_insert(): void
  {
    $data = [
      'brand' => 'testBrand',
      'model' => 'testModel'
    ];

    $res = $this->rentalRepo->store($data);
    $row = $this->db->fetch("SELECT * FROM rental");

    $this->assertSame(1, $res);
    $this->assertSame('testBrand', $row['brand']);
    $this->assertSame('testModel', $row['model']);
    $this->assertSame(null, $row['mainte_date']);
    $this->assertSame(0, $row['del_flag']);
  }

  #[Test]
  public function get_can_fetch_all(): void
  {
    $this->seedRental();

    $res = $this->rentalRepo->get();

    $this->assertCount(2, $res);
    $this->assertSame('Callaway', $res[0]['brand']);
    $this->assertSame('TaylorMade', $res[1]['brand']);
  }

  #[Test]
  public function get_empty_record(): void
  {
    $res = $this->rentalRepo->get();

    $this->assertSame([], $res);
  }
  #[Test]
  public function exists_with_valid_id(): void
  {
    $this->seedRental();

    $res = $this->rentalRepo->exists(1);

    $this->assertTrue($res);
  }
  #[Test]
  public function exists_with_invalid_id(): void
  {
    $this->seedRental();

    $res = $this->rentalRepo->exists(3);

    $this->assertFalse($res);
  }

  #[Test]
  public function isAvailableDate_with_not_mainte_date(): void
  {
    $this->seedRental();
    $id = 2;
    $date = '2025-04-23';

    $res = $this->rentalRepo->isAvailableDate($id, $date);

    $this->assertTrue($res);
  }

  #[Test]
  public function isAvailableDate_with_mainte_date(): void
  {
    $this->seedRental();

    $id = 2;
    $date = '2025-04-22';

    $res = $this->rentalRepo->isAvailableDate($id, $date);

    $this->assertFalse($res);
  }

  #[Test]
  public function getById_with_id(): void
  {
    $this->seedRental();
    $id = 2;
    $res = $this->rentalRepo->getById($id);

    $this->assertSame([
      'brand' => 'Callaway',
      'model' => 'elite(1W,3W,5-9I,PW,SW)',
      'mainte_date' => '2025-04-22'
    ], $res);
  }

  private function seedRental(): void
  {
    $sql = "INSERT INTO rental (id, brand, model, mainte_date)
        VALUES
        (1, 'TaylorMade', 'Q153(1W,3W,5-9I,PW,SW) ', '2025-04-21'),
        (2, 'Callaway', 'elite(1W,3W,5-9I,PW,SW)', '2025-04-22')";
    $this->db->execute($sql);
  }
}