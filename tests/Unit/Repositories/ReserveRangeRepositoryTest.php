<?php
require_once __DIR__ . '/BaseRepositoryTestCase.php';
use App\Database\DbConnect;
use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ReserveRangeRepository;
use App\Repositories\ReserveRentalRepository;
use App\Repositories\ReserveShowerRepository;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReserveRangeRepositoryTest extends BaseRepositoryTestCase
{
  private ?ReserveRangeRepository $reserveRangeRepo = null;
  private ?ReserveRentalRepository $reserveRentalRepo = null;
  private ?ReserveShowerRepository $reserveShowerRepo = null;
  private ?RangeRepository $rangeRepo = null;
  private ?RentalRepository $rentalRepo = null;

  protected function setUp(): void
  {
    $this->db = new DbConnect($this->createPdo());
    $this->reserveRangeRepo = new ReserveRangeRepository($this->db);
    $this->reserveRentalRepo = new ReserveRentalRepository($this->db);
    $this->reserveShowerRepo = new ReserveShowerRepository($this->db);
    $this->rangeRepo = new RangeRepository($this->db);
    $this->rentalRepo = new RentalRepository($this->db);

    $this->createDrivingRange($this->db);
    $this->createRental($this->db);
    $this->createReserveRange($this->db);
    $this->createReserveRental($this->db);
    $this->createReserveShower($this->db);
  }

  protected function tearDown(): void
  {
      $this->db = null;
      $this->reserveRangeRepo = null;
  }  

  #[Test]
  public function getRangeReservation_returns_all_ranges_with_reservations_or_nulls(): void
  {
    $sql = "INSERT INTO drivingRange (name, mainte_date, del_flag)
            VALUES
            ('range1', '2025-04-19', 0),
            ('range2', '2025-04-20', 0),
            ('range3', '2025-04-18', 0)";
    $this->db->execute($sql);

    $sql = "INSERT INTO reserveRange
            (user_id, drivingRange_id, reserve_date, start_time, end_time, number, cancelled, fee)
            VALUES
            (1, 1, '2025-04-14', '13:00:00', '14:30:00', 2, 0, 1200),
            (1, 2, '2025-04-17', '15:00:00', '16:00:00', 1, 0, 800),
            (1, 1, '2025-04-23', '14:00:00', '15:30:00', 1, 0, 1200)";
    $this->db->execute($sql);

    $res = $this->reserveRangeRepo->getRangeReservation('2025-04-17');

    $this->assertSame(
      [
        'name' => 'range1',
        'mainte_date' => '2025-04-19',
        'start_time' => null,
        'end_time' => null,
        'user_id' => null,
      ], $res[0]);
    $this->assertSame(
      [
        'name' => 'range2',
        'mainte_date' => '2025-04-20',
        'start_time' => '15:00:00',
        'end_time' => '16:00:00',
        'user_id' => 1,
      ], $res[1]);
    $this->assertSame(
      [
        'name' => 'range3',
        'mainte_date' => '2025-04-18',
        'start_time' => null,
        'end_time' => null,
        'user_id' => null,
      ], $res[2]);

  }
  #[Test]
  public function getRangeReservation_returns_all_ranges_with_no_reservation(): void
  {
    $sql = "INSERT INTO drivingRange (name, mainte_date, del_flag)
            VALUES
            ('range1', '2025-04-19', 0),
            ('range2', '2025-04-20', 0),
            ('range3', '2025-04-18', 0)";
    $this->db->execute($sql);

    $res = $this->reserveRangeRepo->getRangeReservation('2025-04-17');

    $this->assertSame(
      [
        'name' => 'range1',
        'mainte_date' => '2025-04-19',
        'start_time' => null,
        'end_time' => null,
        'user_id' => null,
      ], $res[0]);
    $this->assertSame(
      [
        'name' => 'range2',
        'mainte_date' => '2025-04-20',
        'start_time' => null,
        'end_time' => null,
        'user_id' => null,
      ], $res[1]);
    $this->assertSame(
      [
        'name' => 'range3',
        'mainte_date' => '2025-04-18',
        'start_time' => null,
        'end_time' => null,
        'user_id' => null,
      ], $res[2]);
  }

  #[Test]
  public function store_can_store_reservation(): void
  {
    $data = [
      'user_id' => 1,
      'range_id' => 1,
      'reserve_date' => '2025-10-10',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'number' => 2,
      'range_fee' => 1500
    ];

    $res = $this->reserveRangeRepo->store($data);
    $row = $this->db->fetch('SELECT * FROM reserveRange');

    $this->assertSame(1, $res);
    $this->assertSame($data['user_id'], $row['user_id']);
    $this->assertSame($data['range_id'], $row['drivingRange_id']);
    $this->assertSame($data['reserve_date'], $row['reserve_date']);
    $this->assertSame($data['start_time'], $row['start_time']);
    $this->assertSame($data['end_time'], $row['end_time']);
    $this->assertSame($data['number'], $row['number']);
    $this->assertSame($data['range_fee'], $row['fee']);
  }

  #[Test]
  #[DataProvider('timeProvider')]
  public function isAvailableTime_not_duplicate_returns_true(array $timeData): void
  {
    $this->seedReserve();

    $res = $this->reserveRangeRepo->isReservableTime(1, '2025-10-10', $timeData['start_time'], $timeData['end_time'], 1);

    $this->assertTrue($res); 
  }
  #[Test]
  public function isAvailableTime_other_day_returns_true(): void
  {
    $this->seedReserve();

    $res = $this->reserveRangeRepo->isReservableTime(1, '2025-10-11', '12:00:00', '13:00:00', 1);

    $this->assertTrue($res); 
  }

  #[Test]
  #[DataProvider('invalidTimeProvider')]
  public function isAvailableTime_duplicate_returns_false(array $timeData): void
  {
    $this->seedReserve();

    $res = $this->reserveRangeRepo->isReservableTime(1, '2025-10-10', $timeData['start_time'], $timeData['end_time'] ,1);

    $this->assertFalse($res); 
  }
  
  #[Test]
  public function isAvailableTime_update_excludes_self_reservtion(): void
  {
    $this->seedReserve();

    $res = $this->reserveRangeRepo->isReservableTime(1, '2025-10-10', '12:00:00', '13:30:00', 1, 'update');

    $this->assertTrue($res); // 自分の予約は除外される
  }

  #[Test]
  #[DataProvider('inValidTimeProvider')]
  public function isDuplicateSameTime_returns_true_when_exists_sama_time(array $timeData): void
  {
    $this->createMyReserve(1, '2025-10-10', $timeData['start_time'], $timeData['end_time']);

    $res = $this->reserveRangeRepo->isDuplicateSameTime(99, '2025-10-10', '12:00:00', '13:00:00', 1);

    $this->assertTrue($res);
  }

  #[Test]
  #[DataProvider('TimeProvider')]
  public function isDuplicateSameTime_returns_false_when_not_exists_sama_time(array $timeData): void
  {
    $this->createMyReserve(1, '2025-10-10', $timeData['start_time'], $timeData['end_time']);

    $res = $this->reserveRangeRepo->isDuplicateSameTime(99, '2025-10-10', '12:00:00', '13:00:00', 1);

    $this->assertFalse($res);
  }

  #[Test]
  public function getByUserId_can_get_within_edge_data(): void
  {
    $this->createMyReserve(1, '2025-10-10', '11:00:00', '12:30:00', 1, '12:30:00');
    $this->createMyReserve(1, '2025-10-11', '13:00:00', '14:30:00', 1, '14:30:00');
    $this->createMyReserve(1, '2025-10-12', '09:00:00', '11:00:00');

    // 2番目のデータがギリギリ入る
    $res = $this->reserveRangeRepo->getByUserId(1, '2025-10-11', '14:29:59');

    $this->assertCount(2, $res);
    $this->assertSame('2025-10-11', $res[0]['reserve_date']);
    $this->assertSame('13:00:00', $res[0]['start_time']);
    $this->assertSame('2025-10-12', $res[1]['reserve_date']);
    $this->assertSame('09:00:00', $res[1]['start_time']);
  }
  #[Test]
  public function getByUserId_can_get_over_edge_data(): void
  {
    $this->createMyReserve(1, '2025-10-10', '11:00:00', '12:30:00', 1, '12:30:00');
    $this->createMyReserve(1, '2025-10-11', '13:00:00', '14:30:00', 1, '14:30:00');
    $this->createMyReserve(1, '2025-10-12', '09:00:00', '11:00:00');

    // 2番目のデータがギリギリ入らない
    $res = $this->reserveRangeRepo->getByUserId(1, '2025-10-11', '14:30:00');

    $this->assertCount(1, $res);
    $this->assertSame('2025-10-12', $res[0]['reserve_date']);
    $this->assertSame('09:00:00', $res[0]['start_time']);
  }
  #[Test]
  public function getByUserId_can_get_collect_data_not_option(): void
  {
    $this->createMyReserve(1, '2025-10-10', '11:00:00', '12:30:00');
    $this->createMyReserve(1, '2025-10-11', '13:00:00', '14:30:00');
    $this->createMyReserve(1, '2025-10-12', '09:00:00', '11:00:00');

    $res = $this->reserveRangeRepo->getByUserId(1, '2025-10-09', '09:00:00');

    $this->assertCount(3, $res);
    $this->assertNull($res[0]['brand']);
    $this->assertNull($res[0]['shower_time']);
    $this->assertNull($res[1]['brand']);
    $this->assertNull($res[1]['shower_time']);
    $this->assertNull($res[2]['brand']);
    $this->assertNull($res[2]['shower_time']);
  }
  #[Test]
  public function getByUserId_can_get_only_own(): void
  {
    $this->createMyReserve(1, '2025-10-10', '11:00:00', '12:30:00');
    $this->createMyReserve(2, '2025-10-11', '13:00:00', '14:30:00');
    $this->createMyReserve(1, '2025-10-12', '09:00:00', '11:00:00');

    $res = $this->reserveRangeRepo->getByUserId(1, '2025-10-09', '09:00:00');

    $this->assertCount(2, $res);
    $this->assertSame('2025-10-10', $res[0]['reserve_date']);
    $this->assertSame('2025-10-12', $res[1]['reserve_date']);
  }
  #[Test]
  public function getByUserId_excludes_cancelled_reservations(): void
  {
    $this->createMyReserve(1, '2025-10-10', '11:00:00', '12:30:00');
    $sql = "UPDATE reserveRange SET cancelled = 1 WHERE user_id = 1";
    $this->db->execute($sql);

    $res = $this->reserveRangeRepo->getByUserId(1, '2025-10-09', '09:00:00');
    $this->assertCount(0, $res);
  }
  #[Test]
  public function getByUserId_excludes_cancelled_option(): void
  {
    $this->createMyReserve(1, '2025-10-10', '11:00:00', '12:30:00', 1, '12:30:00');
    $this->db->execute("UPDATE reserveRental SET cancelled = 1");
    $this->db->execute("UPDATE reserveShower SET cancelled = 1");

    $res = $this->reserveRangeRepo->getByUserId(1, '2025-10-09', '09:00:00');
    $this->assertNotNull($res[0]['range_name']);
    $this->assertNull($res[0]['brand']);
    $this->assertNull($res[0]['shower_time']);
  }
  #[Test]
  public function getByUserId_no_reservation_returns_empty_array(): void
  {
    $res = $this->reserveRangeRepo->getByUserId(1, '2025-10-09', '09:00:00');
    $this->assertSame([], $res);
  }
  #[Test]
  public function getById_can_get_reservation_with_option_by_id(): void
  {
    $id = $this->createMyReserve(1, '2025-10-10', '12:00:00', '13:00:00', 1, '13:00:00');

    $res = $this->reserveRangeRepo->getById($id);

    $this->assertSame(1, $res['range_id']);
    $this->assertSame('2025-10-10', $res['reserve_date']);
    $this->assertSame('12:00:00', $res['start_time']);
    $this->assertSame('13:00:00', $res['end_time']);
    $this->assertArrayHasKey('rental_id', $res);
    $this->assertSame('13:00:00', $res['shower_time']);
  }
  #[Test]
  public function getById_can_get_reservation_with_no_option_by_id(): void
  {
    $id = $this->createMyReserve(1, '2025-10-10', '12:00:00', '13:00:00');

    $res = $this->reserveRangeRepo->getById($id);

    $this->assertSame(1, $res['range_id']);
    $this->assertSame('2025-10-10', $res['reserve_date']);
    $this->assertSame('12:00:00', $res['start_time']);
    $this->assertSame('13:00:00', $res['end_time']);
    $this->assertNull($res['rental_id']);
    $this->assertNull($res['shower_time']);
  }
  #[Test]
  public function getById_returns_false_by_not_exist_id(): void
  {
    $res = $this->reserveRangeRepo->getById(99);

    $this->assertFalse($res);
  }
  #[Test]
  public function update_can_update(): void
  {
    $id = $this->createMyReserve(1, '2025-10-10', '12:00:00', '13:00:00');
    $data = [
      'range_id' => 2,
      'reserve_date' => '2025-10-12',
      'start_time' => '09:00:00',
      'end_time' => '11:30:00',
      'number' => 2,
      'range_fee' => 2200,
    ];

    $res = $this->reserveRangeRepo->update($id, $data);
    $updated = $this->db->fetch("SELECT * FROM reserveRange WHERE id = {$id}");

    $this->assertSame(1, $res);
    $this->assertSame($data['range_id'], $updated['drivingRange_id']);
    $this->assertSame($data['reserve_date'], $updated['reserve_date']);
    $this->assertSame($data['start_time'], $updated['start_time']);
    $this->assertSame($data['end_time'], $updated['end_time']);
    $this->assertSame($data['number'], $updated['number']);
    $this->assertSame($data['range_fee'], $updated['fee']);
  }

  #[Test]
  public function cancel_can_cancelled_0_to_1(): void
  {
    $id = $this->createMyReserve(1, '2025-10-10', '12:00:00', '13:00:00');

    $res = $this->reserveRangeRepo->cancel($id);
    $cancelled = $this->db->fetch("SELECT * FROM reserveRange WHERE id = {$id}");

    $this->assertSame(1, $res);
    $this->assertSame(1, $cancelled['cancelled']);
  }

  #[Test]
  public function isOwnerByUser_returns_true_when_valid_user(): void
  {
    $id = $this->createMyReserve(1, '2025-10-10', '12:00:00', '13:00:00');
    $userId = 1;

    $res = $this->reserveRangeRepo->isOwnedByUser($id, $userId);

    $this->assertTrue($res);
  }
  
  #[Test]
  public function isOwnerByUser_returns_false_invalid_user(): void
  {
    $id = $this->createMyReserve(1, '2025-10-10', '12:00:00', '13:00:00');
    $userId = 99;

    $res = $this->reserveRangeRepo->isOwnedByUser($id, $userId);

    $this->assertFalse($res);
  }

  private function seedReserve(): void
  {
    $data = [
      'user_id' => 1,
      'range_id' => 1,
      'reserve_date' => '2025-10-10',
      'start_time' => '12:00:00',
      'end_time' => '13:00:00',
      'number' => 2,
      'range_fee' => 1500
    ];

    $this->reserveRangeRepo->store($data);
  }

  private function createMyReserve(int $userId, string $date, string $start, string $end,
                    ?int $rental = null, ?string $showerTime = null): ?int
  {
    $this->rangeRepo->store('range1');
    $rangeId = $this->db->lastInsertId();

    $rentalData = [
      'brand' => 'testBrand',
      'model' => 'testModel'
    ];
    $this->rentalRepo->store($rentalData);
    $rentalId = $this->db->lastInsertId();

    $reserve = [
      'user_id' => $userId,
      'range_id' => $rangeId,
      'reserve_date' => $date,
      'start_time' => $start,
      'end_time' => $end,
      'number' => 1,
      'range_fee' => 1000
    ];
    $this->reserveRangeRepo->store($reserve);
    $reserveRangeId = $this->db->lastInsertId();

    if($rental !== null) {
      $reserve = [
        'user_id' => $userId,
        'rental_id' => $rentalId,
        'reserveRange_id' => $reserveRangeId,
        'reserve_date' => $date,
        'start_time' => $start,
        'end_time' => $end,
        'rental_fee' => 1000
      ];
      $this->reserveRentalRepo->store($reserve);
    }
    if($showerTime !== null) {
      $reserve = [
        'user_id' => $userId,
        'reserveRange_id' => $reserveRangeId,
        'reserve_date' => $date,
        'shower_time' => $showerTime,
        'shower_end' => Carbon::createFromTimeString($showerTime)->addMinutes(30)->format('H:i:s'),
        'shower_fee' => 1000
      ];
      $this->reserveShowerRepo->store($reserve);
    }

    return $reserveRangeId;
  }



  public static function timeProvider()
  {
    return  [
      [['start_time' => '11:00:00', 'end_time' => '12:00:00']],
      [['start_time' => '13:00:00', 'end_time' => '14:00:00']],
      [['start_time' => '16:00:00', 'end_time' => '18:00:00']],
      [['start_time' => '09:00:00', 'end_time' => '10:00:00']],
    ];
  }
  public static function invalidTimeProvider()
  {
    return  [
      [['start_time' => '11:00:00', 'end_time' => '12:30:00']],
      [['start_time' => '12:30:00', 'end_time' => '14:00:00']],
      [['start_time' => '12:00:00', 'end_time' => '13:00:00']],
      [['start_time' => '11:00:00', 'end_time' => '14:00:00']],
    ];
  }
}