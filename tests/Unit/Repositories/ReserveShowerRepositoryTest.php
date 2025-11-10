<?php

require_once __DIR__ . '/BaseRepositoryTestCase.php';

use App\Database\DbConnect;
use App\Repositories\ReserveShowerRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReserveShowerRepositoryTest extends BaseRepositoryTestCase
{
  private ?ReserveShowerRepository $repo = null;

  protected function setUp(): void
  {
    $this->db = new DbConnect($this->createPdo());
    $this->repo = new ReserveShowerRepository($this->db);
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
  public function store_can_store_reservation(): void
  {
    $data = [
      'user_id' => 1,
      'reserveRange_id' => 2,
      'reserve_date' => '2025-10-10',
      'shower_time' => '08:00:00',
      'shower_end' => '09:00:00',
      'shower_fee' => 1500
    ];

    $res = $this->repo->store($data);
    $row = $this->db->fetch('SELECT * FROM reserveShower');

    $this->assertSame(1, $res);
    $this->assertSame($data['user_id'], $row['user_id']);
    $this->assertSame($data['reserveRange_id'], $row['reserveRange_id']);
    $this->assertSame($data['reserve_date'], $row['reserve_date']);
    $this->assertSame($data['shower_time'], $row['start_time']);
    $this->assertSame($data['shower_end'], $row['end_time']);
    $this->assertSame($data['shower_fee'], $row['fee']);
  }

  #[Test]
  #[DataProvider('timeProvider')]
  public function isAvailableTime_not_duplicate_returns_true($timeData): void
  {
    $this->seedReserve();

    $res = $this->repo->isReservableTime('2025-10-10', $timeData, 1);

    $this->assertTrue($res);
  }

  #[Test]
  public function isAvailableTime_other_day_returns_true(): void
  {
    $this->seedReserve();

    $res = $this->repo->isReservableTime('2025-10-11', '13:00:00', 1);

    $this->assertTrue($res);
  }

  #[Test]
  public function isAvailableTime_duplicate_returns_False(): void
  {
    $this->seedReserve();

    $res = $this->repo->isReservableTime('2025-10-10', '13:00:00', 1);

    $this->assertFalse($res);
  }

  #[Test]
  public function isAvailableTime_update_excludes_self_reservtion(): void
  {
    $this->seedReserve();

    $res = $this->repo->isReservableTime('2025-10-10', '13:30:00', 1, 'update');

    $this->assertTrue($res); // 自分の予約は除外される
  }

  #[Test]
  public function exists_returns_true_when_valid_id(): void
  {
    $id = $this->seedReserve();
    $reserveRangeId = $this->db->fetch("SELECT * FROM reserveShower WHERE id = {$id}")['reserveRange_id'];

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
    $reserveRangeId = $this->db->fetch("SELECT * FROM reserveShower WHERE id = {$id}")['reserveRange_id'];
    $this->db->execute("UPDATE reserveShower SET cancelled = 1 WHERE id = {$id}");

    $res = $this->repo->exists($reserveRangeId);

    $this->assertFalse($res);
  }

  #[Test]
  public function update_can_update(): void
  {
    $id = $this->seedReserve();
    $reserveRangeId = $this->db->fetch("SELECT * FROM reserveShower WHERE id = {$id}")['reserveRange_id'];

    $res = $this->repo->update($reserveRangeId, [
      'reserve_date' => '2025-10-10',
      'shower_time' => '14:00:00', // 変更
      'shower_end' => '14:30:00', // 変更
      'shower_fee' => 1500
    ]);
    $updated = $this->db->fetch("SELECT * FROM reserveShower WHERE reserveRange_id = {$reserveRangeId}");

    $this->assertSame(1, $res);
    $this->assertSame('14:00:00', $updated['start_time']);
    $this->assertSame('14:30:00', $updated['end_time']);
  }

  #[Test]
  public function cancel_can_cancelled(): void
  {
    $id = $this->seedReserve();
    $reserveRangeId = $this->db->fetch("SELECT * FROM reserveShower WHERE id = {$id}")['reserveRange_id'];

    $res = $this->repo->cancel($reserveRangeId);
    $cancelled = $this->db->fetch("SELECT * FROM reserveShower WHERE reserveRange_id = {$reserveRangeId}");

    $this->assertSame(1, $res);
    $this->assertSame(1, $cancelled['cancelled']);
  }

  private function seedReserve(): int
  {
    $data = [
      'user_id' => 1,
      'reserveRange_id' => 2,
      'reserve_date' => '2025-10-10',
      'shower_time' => '13:00:00',
      'shower_end' => '13:30:00',
      'shower_fee' => 1500
    ];

    $this->repo->store($data);

    return $this->db->lastInsertId();
  }

  public static function timeProvider(): array
  {
    return [
      ['12:00:00'], ['12:30:00'], ['13:30:00']
    ];

  }
}