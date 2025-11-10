<?php

use App\Database\DbConnect;
use App\Exceptions\BadRequestException;
use App\Repositories\RangeRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RangeRepositoryTest extends TestCase
{

  private ?DbConnect $db = null;
  private ?RangeRepository $rangeRepo = null;

  protected function setUp(): void
  {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $this->db = new DbConnect($pdo);
    $this->rangeRepo = new RangeRepository($this->db);

    $sql = "CREATE TABLE drivingRange (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL,
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
      $this->rangeRepo = null;
  }

  #[Test]
  public function store_can_insert(): void
  {
    $res = $this->rangeRepo->store('range1');

    $this->assertSame(1, $res);
    $row = $this->db->fetch("SELECT * FROM drivingRange");
    $this->assertSame('range1', $row['name']);
    $this->assertSame(null, $row['mainte_date']);
    $this->assertSame(0, $row['del_flag']);
  }

  #[Test]
  public function get_can_fetch_all(): void
  {
    $sql = "INSERT INTO drivingRange (name, mainte_date, del_flag)
            VALUES
            ('range1', '2025-04-19', 0),
            ('range2', '2025-04-20', 0),
            ('range3', '2025-04-18', 1)";
    $this->db->execute($sql);

    $res = $this->rangeRepo->get();

    $this->assertCount(2, $res);
    $this->assertSame('range1', $res[0]['name']);
    $this->assertSame('range2', $res[1]['name']);
  }

  #[Test]
  public function get_empty_record(): void
  {
    $res = $this->rangeRepo->get();

    $this->assertSame([], $res);
  }

  #[Test]
  public function exists_with_valid_id(): void
  {
    $sql = "INSERT INTO drivingRange (id, name, mainte_date, del_flag)
        VALUES
        (1, 'range1', '2025-04-19', 0),
        (2, 'range2', '2025-04-20', 0),
        (3, 'range3', '2025-04-18', 1)";
    $this->db->execute($sql);
    
    $res = $this->rangeRepo->exists(1);

    $this->assertTrue($res);
  }
  #[Test]
  public function exists_with_invalid_id(): void
  {
    $sql = "INSERT INTO drivingRange (id, name, mainte_date, del_flag)
        VALUES
        (1, 'range1', '2025-04-19', 0),
        (2, 'range2', '2025-04-20', 0),
        (3, 'range3', '2025-04-18', 1)";
    $this->db->execute($sql);

    $res = $this->rangeRepo->exists(4);

    $this->assertFalse($res);
  }
  #[Test]
  public function exists_with_delete_id(): void
  {
    $sql = "INSERT INTO drivingRange (id, name, mainte_date, del_flag)
        VALUES
        (1, 'range1', '2025-04-19', 0),
        (2, 'range2', '2025-04-20', 0),
        (3, 'range3', '2025-04-18', 1)";
    $this->db->execute($sql);

    $res = $this->rangeRepo->exists(3);

    $this->assertFalse($res);
  }

  #[Test]
  public function isAvailableDate_with_not_mainte_date(): void
  {
    $sql = "INSERT INTO drivingRange (id, name, mainte_date, del_flag)
        VALUES
        (1, 'range1', '2025-04-19', 0),
        (2, 'range2', '2025-04-20', 0)";

    $this->db->execute($sql);

    $id = 2;
    $date = '2025-04-21';

    $res = $this->rangeRepo->isAvailableDate($id, $date);

    $this->assertTrue($res);
  }

  #[Test]
  public function isAvailableDate_with_mainte_date(): void
  {
    $sql = "INSERT INTO drivingRange (id, name, mainte_date, del_flag)
        VALUES
        (1, 'range1', '2025-04-19', 0),
        (2, 'range2', '2025-04-20', 0)";

    $this->db->execute($sql);

    $id = 2;
    $date = '2025-04-20';

    $res = $this->rangeRepo->isAvailableDate($id, $date);

    $this->assertFalse($res);
  }

  #[Test]
  public function getById_with_id(): void
  {
    $sql = "INSERT INTO drivingRange (name, mainte_date, del_flag)
            VALUES
            ('range1', '2025-04-19', 0),
            ('range2', '2025-04-20', 0),
            ('range3', '2025-04-18', 1)";
    $this->db->execute($sql);

    $this->assertSame('range1', $this->rangeRepo->getById(1)['name']);
    $this->assertSame('range2', $this->rangeRepo->getById(2)['name']);
    $this->assertFalse($this->rangeRepo->getById(3));
  }
}