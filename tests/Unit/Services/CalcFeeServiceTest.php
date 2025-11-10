<?php
require_once __DIR__ . '/../Repositories/BaseRepositoryTestCase.php';

use App\Database\DbConnect;
use App\Repositories\FeeRepository;
use App\Services\User\CalcFeeService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CalcFeeServiceTest extends BaseRepositoryTestCase
{
  private CalcFeeService $service;
  private array $req;

  protected function setUp(): void
  {
    $pdo = $this->createPdo();
    $this->db = new DbConnect($pdo);

    $this->service = new CalcFeeService(new FeeRepository($this->db));
    
    $this->createRangeFee($this->db);
    $this->createRentalFee($this->db);
    $this->createShowerFee($this->db);

    $this->req = [
      'number' => 2, 
      'reserve_date' => '',
      'start_time' => '10:00:00',
      'end_time' => '11:30:00',
      'rental' => 1,
      'shower' => 1,
      'shower_time' => '12:00:00'
    ];
  }


  #[Test]
  public function calcFee_calc_weekday_with_option_when_current(): void
  {
    $baseDate = '2025-10-15'; // 平日
    $this->setFee($baseDate);

    $this->req['reserve_date'] = $baseDate;

    $res = $this->service->calcFee($this->req);

    $this->assertEquals($this->expectedFee(1000, 1200, 1050, 500, 1.5), $res);
  }
  #[Test]
  public function calcFee_calc_weekday_with_option_when_new_fee(): void
  {
    $baseDate = '2025-10-15'; // 平日
    $this->setFee($baseDate);

    $this->req['reserve_date'] = Carbon::parse($baseDate)->addDays(14)->format('Y-m-d');

    $res = $this->service->calcFee($this->req);

    $this->assertEquals($this->expectedFee(1400, 1500, 1350, 600, 1.5), $res);
  }

  #[Test]
  public function calcFee_calc_holiday_with_option_when_current(): void
  {
    $baseDate = '2025-10-18'; // 土日
    $this->setFee($baseDate);
    $this->req['reserve_date'] = $baseDate;

    $res = $this->service->calcFee($this->req);

    $expect = [
      'entrance_fee' => 1000,
      'range_hourly_fee' => 1500,
      'rental_fee' => 1050,
      'shower_fee' => 500,
      'usage_time' => 1.5
    ];

    $this->assertEquals($this->expectedFee(1000, 1500, 1050, 500, 1.5), $res);
  }
  #[Test]
  public function calcFee_calc_holiday_with_option_when_new_fee(): void
  {
    $baseDate = '2025-10-18'; // 土日
    $this->setFee($baseDate);
    $this->req['reserve_date'] = Carbon::parse($baseDate)->addDays(14)->format('Y-m-d');

    $res = $this->service->calcFee($this->req);

    $expect = [
      'entrance_fee' => 1400,
      'range_hourly_fee' => 1800,
      'rental_fee' => 1350,
      'shower_fee' => 600,
      'usage_time' => 1.5
    ];

    $this->assertEquals($this->expectedFee(1400, 1800, 1350, 600, 1.5), $res);
  }

  #[Test]
  public function calcFee_calc_weekday_with_not_option_when_current(): void
  {
    $baseDate = '2025-10-15'; // 平日
    $this->setFee($baseDate);
    $req = [
      'number' => 2, 
      'reserve_date' => $baseDate,
      'start_time' => '10:00:00',
      'end_time' => '11:30:00',
    ];

    $res = $this->service->calcFee($req);

    $expect = [
      'entrance_fee' => 1000,
      'range_hourly_fee' => 1200,
      'rental_fee' => 0,
      'shower_fee' => 0,
      'usage_time' => 1.5
    ];

    $this->assertEquals($this->expectedFee(1000, 1200, 0, 0, 1.5), $res);
  }

  #[Test]
  public function calcFee_uses_new_fee_on_effective_date(): void
  {
      $baseDate = '2025-10-15';
      $this->setFee($baseDate);
      $this->req['reserve_date'] = '2025-10-25'; // after10 の改定日ぴったり

      $res = $this->service->calcFee($this->req);

      $this->assertSame(1400, $res['entrance_fee']); // 新料金が適用される
  }

  private function setFee(string $date): void
  {
    $baseDate = Carbon::parse($date);
    $before50 = $baseDate->copy()->addDays(-50)->format('Y-m-d');
    $after10 = $baseDate->copy()->addDays(10)->format('Y-m-d');

    $sql = "INSERT INTO rangeFee (entrance_fee, weekday_fee, holiday_fee, effective_date)
            VALUES
            (500, 800, 1000, :before50),
            (700, 1000, 1200, :after10)";
    
    $this->db->execute($sql, [
      'before50' => $before50, 'after10' => $after10
    ]);

    $sql = "INSERT INTO rentalFee (rental_fee, effective_date)
            VALUES
            (700, :before50),
            (900, :after10)";
    
    $this->db->execute($sql, [
      'before50' => $before50, 'after10' => $after10
    ]);

    $sql = "INSERT INTO showerFee (shower_fee, effective_date)
            VALUES
            (500, :before50),
            (600, :after10)";
    
    $this->db->execute($sql, [
      'before50' => $before50, 'after10' => $after10
    ]);
  }

  private function expectedFee(int $entrance, int $range, int $rental, int $shower, float $usage): array
  {
    return [
      'entrance_fee' => $entrance,
      'range_hourly_fee' => $range,
      'rental_fee' => $rental,
      'shower_fee' => $shower,
      'usage_time' => $usage
    ];
  }
}