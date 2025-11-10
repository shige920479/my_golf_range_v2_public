<?php
require_once __DIR__ . '/BaseRepositoryTestCase.php';

use App\Database\DbConnect;
use App\Repositories\ReserveRentalRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReserveRentalRepositoryTest extends BaseRepositoryTestCase
{
  private ?ReserveRentalRepository $repo = null;

  protected function setUp(): void
  {
    $this->db = new DbConnect($this->createPdo());
    $this->repo = new ReserveRentalRepository($this->db);
    $this->createRental($this->db);
    $this->createShower($this->db);
    $this->createReserveRental($this->db);
    $this->createReserveShower($this->db);
  }

  protected function tearDown(): void
  {
      $this->db = null;
      $this->repo = null;
  }  

  #[Test]
  public function unionRentalAndShower_returns_all_ranges_with_reservations_or_nulls():void
  {
    $sql = "INSERT INTO rental (brand, model, mainte_date, del_flag)
            VALUES
            ('TaylorMade', 'Q153(1W,3W,5-9I,PW,SW)', '2025-04-21', 0),
            ('Callaway', 'elite(1W,3W,5-9I,PW,SW)', '2025-04-22', 0)";
    $this->db->execute($sql);
    
    $sql = "INSERT INTO shower (mainte_date, del_flag)
            VALUES ('2025-04-23', 0)";
    $this->db->execute($sql);

    $sql = "INSERT INTO reserveRental
            (user_id, rental_id, reserveRange_id, reserve_date, start_time, end_time, cancelled, fee)
            VALUES
            (1, 1, 1, '2025-04-14', '13:00:00', '14:30:00', 0, 750),
            (1, 2, 2, '2025-04-17', '15:00:00', '16:00:00', 0, 500)";
    $this->db->execute($sql);

    $sql = "INSERT INTO reserveShower (user_id, reserveRange_id, reserve_date, start_time, end_time, cancelled, fee)
            VALUES
            (1, 1, '2025-04-14', '14:30:00', '15:00:00', 0, 300)";
    $this->db->execute($sql);

    $res = $this->repo->unionRentalAndShowerReservation('2025-04-14');

    $this->assertSame([
      [
        'name' => 'Callaway/elite(1W,3W,5-9I,PW,SW)',
        'mainte_date' => '2025-04-22',
        'start_time' => null,
        'end_time' => null,
        'user_id' => null,
      ],
      [
        'name' => 'TaylorMade/Q153(1W,3W,5-9I,PW,SW)',
        'mainte_date' => '2025-04-21',
        'start_time' => '13:00:00',
        'end_time' => '14:30:00',
        'user_id' => 1,
      ],
      [
        'name' => 'シャワールーム',
        'mainte_date' => '2025-04-23',
        'start_time' => '14:30:00',
        'end_time' => '15:00:00',
        'user_id' => 1,
      ]
    ], $res);
  }

  #[Test]
  public function store_can_store_reservation(): void
  {
    $data = [
      'user_id' => 1,
      'rental_id' => 1,
      'reserveRange_id' => 2,
      'reserve_date' => '2025-10-10',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'rental_fee' => 1500
    ];

    $res = $this->repo->store($data);
    $row = $this->db->fetch('SELECT * FROM reserveRental');

    $this->assertSame(1, $res);
    $this->assertSame($data['user_id'], $row['user_id']);
    $this->assertSame($data['rental_id'], $row['rental_id']);
    $this->assertSame($data['reserveRange_id'], $row['reserveRange_id']);
    $this->assertSame($data['reserve_date'], $row['reserve_date']);
    $this->assertSame($data['start_time'], $row['start_time']);
    $this->assertSame($data['end_time'], $row['end_time']);
    $this->assertSame($data['rental_fee'], $row['fee']);
  }

  #[Test]
  #[DataProvider('timeProvider')]
  public function isAvailableTime_not_duplicate_returns_true(array $timeData): void
  {
    $this->seedReserve();

    $res = $this->repo->isReservableTime(1, '2025-10-10', $timeData['start_time'], $timeData['end_time'], 1);

    $this->assertTrue($res); 
  }

  #[Test]
  public function isAvailableTime_other_days_returns_true(): void
  {
    $this->seedReserve();

    $res = $this->repo->isReservableTime(1, '2025-10-11', '09:00:00', '18:00:00', 1);

    $this->assertTrue($res); 
  }

  #[Test]
  #[DataProvider('invalidTimeProvider')]
  public function isAvailableTime_duplicate_returns_false(array $timeData): void
  {
    $this->seedReserve();

    $res = $this->repo->isReservableTime(1, '2025-10-10', $timeData['start_time'], $timeData['end_time'] , 1);

    $this->assertFalse($res); 
  }

  #[Test]
  public function isAvailableTime_update_excludes_self_reservtion(): void
  {
    $this->seedReserve();

    $res = $this->repo->isReservableTime(1, '2025-10-10', '12:00:00', '13:30:00', 1, 'update');

    $this->assertTrue($res); // 自分の予約は除外される
  }

  #[Test]
  public function exists_returns_true_when_valid_id(): void
  {
    $id = $this->seedReserve();
    $reserveRangeId = $this->db->fetch("SELECT * FROM reserveRental WHERE id = {$id}")['reserveRange_id'];

    $res = $this->repo->exists($reserveRangeId);

    $this->assertTrue($res);
  }
  
  #[Test]
  public function exists_returns_false_when_invalid_id(): void
  {
    $reserveRangeId = 99;

    $res = $this->repo->exists($reserveRangeId);

    $this->assertFalse($res);
  }

  #[Test]
  public function exists_returns_false_when_cancelled(): void
  {
    $id = $this->seedReserve();
    $reserveRangeId = $this->db->fetch("SELECT * FROM reserveRental WHERE id = {$id}")['reserveRange_id'];
    $this->db->execute("UPDATE reserveRental SET cancelled = 1 WHERE id = {$id}");

    $res = $this->repo->exists($reserveRangeId);

    $this->assertFalse($res);
  }

  #[Test]
  public function update_can_update(): void
  {
    $id = $this->seedReserve();
    $reserveRangeId = $this->db->fetch("SELECT * FROM reserveRental WHERE id = {$id}")['reserveRange_id'];

    $res = $this->repo->update($reserveRangeId, [
      'rental_id' => 2, // 変更
      'reserve_date' => '2025-10-10',
      'start_time' => '12:00:00',
      'end_time' => '14:30:00', // 変更
      'rental_fee' => 1500
    ]);
    $updated = $this->db->fetch("SELECT * FROM reserveRental WHERE reserveRange_id = {$reserveRangeId}");

    $this->assertSame(1, $res);
    $this->assertSame(2, $updated['rental_id']);
    $this->assertSame('14:30:00', $updated['end_time']);
  }

  #[Test]
  public function cancel_can_cancelled(): void
  {
    $id = $this->seedReserve();
    $reserveRangeId = $this->db->fetch("SELECT * FROM reserveRental WHERE id = {$id}")['reserveRange_id'];

    $res = $this->repo->cancel($reserveRangeId);
    $cancelled = $this->db->fetch("SELECT * FROM reserveRental WHERE reserveRange_id = {$reserveRangeId}");

    $this->assertSame(1, $res);
    $this->assertSame(1, $cancelled['cancelled']);
  }

  private function seedReserve(): int
  {
    $data = [
      'user_id' => 1,
      'rental_id' => 1,
      'reserveRange_id' => 2,
      'reserve_date' => '2025-10-10',
      'start_time' => '12:00:00',
      'end_time' => '13:00:00',
      'rental_fee' => 1500
    ];
    
    $this->repo->store($data);
    return $this->db->lastInsertId();
  }


  public static function timeProvider()
  {
    return  [
      [['start_time' => '11:00:00', 'end_time' => '12:00:00']],
      [['start_time' => '13:00:00','end_time' => '14:00:00']],
      [['start_time' => '16:00:00','end_time' => '18:00:00']],
      [['start_time' => '09:00:00','end_time' => '10:00:00']],
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