<?php
require_once __DIR__ . '/../../helpers/ReflectionHelper.php';
use App\Services\User\ReserveTableService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ReserveTableServiceTest extends TestCase
{
  use ReflectionHelper;

  private ReserveTableService $service;
  private array $timeSlots;

  protected function setUp(): void
  {
    $this->service = new ReserveTableService();

    $this->timeSlots = [
      ['value' => '08:00:00', 'label' => '08:00'],
      ['value' => '08:30:00', 'label' => '08:30'],
      ['value' => '09:00:00', 'label' => '09:00'],
    ];

  }

  #[Test]
  public function getMatrix_builds_correct_matrix(): void
  {

    $date = '2025-10-08';
    $mainte1 = '2025-10-09';
    $mainte2 = '2025-10-10';

    $userId1 = 1;
    $userId2 = 99;

    $rows = $this->reservations($mainte1, $mainte2, $userId1, $userId2);

    $res = $this->service->getMatrix($this->timeSlots, $rows, $date, $userId1);
    $matrix = [];
    $matrix = $res['rangeA'];

    $this->assertArrayHasKey('rangeA', $res);
    $this->assertCount(count($this->timeSlots), $matrix);

    $this->assertEquals('own', $matrix[0]['status']);
    $this->assertEquals('other', $matrix[1]['status']);
    $this->assertEquals('empty', $matrix[2]['status']);
  }
#[Test]
  public function getMatrix_handles_multiple_facilities(): void
  {
    $date = '2025-10-08';
    $mainte1 = '2025-10-09';
    $mainte2 = '2025-10-10';
    $userId1 = 1;
    $userId2 = 99;
    
    $rows = $this->reservations($mainte1, $mainte2, $userId1, $userId2);
    $rows[1]['name'] = 'rangeB';

    $res = $this->service->getMatrix($this->timeSlots, $rows, $date, $userId1);

    $this->assertArrayHasKey('rangeA', $res);
    $this->assertArrayHasKey('rangeB', $res);
    $this->assertNotEmpty($res['rangeA']);
    $this->assertNotEmpty($res['rangeB']);
  }

  #[Test]
  public function getMatrix_builds_correct_matrix_with_mainte():void
  {
    $date = '2025-10-09';
    $mainte1 = '2025-10-09';
    $mainte2 = '2025-10-10';

    $userId1 = 1;
    $userId2 = 99;

    $rows = $this->reservations($mainte1, $mainte2, $userId1, $userId2);

    $res = $this->service->getMatrix($this->timeSlots, $rows, $date, $userId1);
    $matrix = [];
    $matrix = $res['rangeA'];

    $this->assertArrayHasKey('rangeA', $res);
    $this->assertCount(count($this->timeSlots), $matrix);
    foreach($matrix as $slot) {
      $this->assertSame('mainte', $slot['status']);
    }
  }
  #[Test]
  public function getMatrix_returns_empty_when_no_reservations(): void
  {
    $res = $this->service->getMatrix($this->timeSlots, [], '2025-10-10', 1);
    
    $this->assertSame([], $res);
  }

  #[Test]
  public function it_groups_reservations_by_facility_name(): void
  {

    $reservations = [
        ['name' => 'rangeA', 'mainte_date' => 'x', 'start_time' => '08:00', 'end_time' => '08:30', 'user_id' => 1],
        ['name' => 'rangeA', 'mainte_date' => 'x', 'start_time' => '08:30', 'end_time' => '09:00', 'user_id' => 1],
        ['name' => 'club', 'mainte_date' => 'x', 'start_time' => '08:00', 'end_time' => '09:00', 'user_id' => 1],
    ];
    
    $res = $this->callMethod($this->service, 'arrangeByFacility', [$reservations]);

    $this->assertCount(2, $res);
    $this->assertCount(2, $res['rangeA']);
    $this->assertCount(1, $res['club']);
  }

  #[Test]
  public function buildReserveMatrix_returns_empty_when_no_reservations(): void
{
    $tables = [];
    $res = $this->callMethod($this->service, 'buildReserveMatrix', [$this->timeSlots, $tables, '2025-10-10', 1]);

    foreach ($res as $slot) {
        $this->assertSame('empty', $slot['status']);
    }
}

  /** 予約データ */
  private function reservations(string $mainte1, string $mainte2, int $userId1, int $userId2): array
  {
    return [
      [
        'name' => 'rangeA',
        'mainte_date' => $mainte1,
        'start_time' => '08:00:00',
        'end_time'   => '08:30:00',
        'user_id'    => $userId1,
      ],
      [
        'name' => 'rangeA',
        'mainte_date' => $mainte2,
        'start_time' => '08:30:00',
        'end_time'   => '09:00:00',
        'user_id'    => $userId2,
      ],
    ];
  }
  
}