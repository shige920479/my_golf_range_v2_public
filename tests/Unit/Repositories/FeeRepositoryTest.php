<?php

use App\Database\DbConnect;
use App\Repositories\FeeRepository;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FeeRepositoryTest extends TestCase
{
  private ?DbConnect $db = null;
  private ?FeeRepository $feeRepo = null;

  protected function setUp(): void
  {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $this->db = new DbConnect($pdo);
    $this->feeRepo = new FeeRepository($this->db);

    $now = Carbon::today();
    $before90 = $now->copy()->addDays(-90)->format('Y-m-d');
    $before60 = $now->copy()->addDays(-60)->format('Y-m-d');
    $today = $now->copy()->format('Y-m-d');
    $after30 = $now->copy()->addDays(30)->format('Y-m-d');

    $sql = "CREATE TABLE rangeFee (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            entrance_fee DECIMAL(10, 2) NOT NULL,
            weekday_fee DECIMAL(10, 2) NOT NULL, 
            holiday_fee DECIMAL(10, 2) NOT NULL,
            effective_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
    $this->db->execute($sql);

    $sql = "INSERT INTO rangeFee (entrance_fee, weekday_fee, holiday_fee, effective_date)
            VALUES
            (100, 200, 300, :before90),
            (400, 500, 600, :before60),
            (700, 800, 900, :today),
            (1000, 1100, 1200, :after30)
            ";
    
    $this->db->execute($sql, [
      'before90' => $before90, 'before60' => $before60, 'today' => $today, 'after30' => $after30
    ]);
  }

  protected function tearDown(): void
  {
      $this->db = null;
      $this->feeRepo = null;
  }

  #[Test]
  public function getCurrentFee_can_get_current_fee(): void
  {
    $fee = $this->feeRepo->getCurrentFee('rangeFee');
    
    $this->assertSame(700, (int)$fee['entrance_fee']);
    $this->assertSame(800, (int)$fee['weekday_fee']);
    $this->assertSame(900, (int)$fee['holiday_fee']);
  }

  #[Test]
  public function getChangeFee_can_get_future_fee(): void
  {
    $fee = $this->feeRepo->getChangeFee('rangeFee');
    
    $this->assertSame(1000, (int)$fee['entrance_fee']);
    $this->assertSame(1100, (int)$fee['weekday_fee']);
    $this->assertSame(1200, (int)$fee['holiday_fee']);
  }
  #[Test]
  public function it_can_get_current_fee_not_exsists_change_fee(): void
  {
    $after30 = Carbon::today()->addDays(30)->format('Y-m-d');
    $sql = "DELETE FROM rangeFee WHERE effective_date = :after30";
    $this->db->execute($sql, ['after30' => $after30]);

    $currentFee = $this->feeRepo->getCurrentFee('rangeFee');
    $futureFee = $this->feeRepo->getChangeFee('rangeFee');

    $this->assertSame(700, (int)$currentFee['entrance_fee']);
    $this->assertFalse($futureFee);
  }
  #[Test]
  public function getCurrentFee_throws_exception_for_invalid_table(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->feeRepo->getCurrentFee('invalidTable');
  }
  #[Test]
  public function getChangeFee_throws_exception_for_invalid_table(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->feeRepo->getChangeFee('invalidTable');
  }
  #[Test]
  public function getFee_throws_exception_for_invalid_table(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->feeRepo->getFee('invalidTable', '2025-10-10');
  }
  
  #[Test]
  public function getFee_can_get_today_fee(): void
  {
    $date = Carbon::today()->format('Y-m-d');
    $table = 'rangeFee';

    $res = $this->feeRepo->getFee($table, $date);

    $this->assertSame(700, (int)$res['entrance_fee']);
    $this->assertSame(800, (int)$res['weekday_fee']);
    $this->assertSame(900, (int)$res['holiday_fee']);
  }
  #[Test]
  public function getFee_can_get_current_fee(): void
  {
    $date = Carbon::today()->addDays(-10)->format('Y-m-d');
    $table = 'rangeFee';

    $res = $this->feeRepo->getFee($table, $date);

    $this->assertSame(400, (int)$res['entrance_fee']);
    $this->assertSame(500, (int)$res['weekday_fee']);
    $this->assertSame(600, (int)$res['holiday_fee']);
  }
  #[Test]
  public function getFee_can_get_new_fee(): void
  {
    $date = Carbon::today()->addDays(30)->format('Y-m-d');
    $table = 'rangeFee';

    $res = $this->feeRepo->getFee($table, $date);

    $this->assertSame(1000, (int)$res['entrance_fee']);
    $this->assertSame(1100, (int)$res['weekday_fee']);
    $this->assertSame(1200, (int)$res['holiday_fee']);
  }
  #[Test]
  public function getFee_can_get_old_fee(): void
  {
    $date = Carbon::today()->addDays(-61)->format('Y-m-d');
    $table = 'rangeFee';

    $res = $this->feeRepo->getFee($table, $date);

    $this->assertSame(100, (int)$res['entrance_fee']);
    $this->assertSame(200, (int)$res['weekday_fee']);
    $this->assertSame(300, (int)$res['holiday_fee']);
  }

}