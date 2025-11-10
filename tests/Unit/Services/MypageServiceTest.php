<?php

use App\Repositories\ReserveRangeRepository;
use App\Services\User\MypageService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MypageServiceTest extends TestCase
{
  /** @var MockObject&ReserveRangeRepository */
  private ReserveRangeRepository $reserveRangeRepo;
  private MypageService $service;

  protected function setUp(): void
  {
    $this->reserveRangeRepo = $this->createMock(ReserveRangeRepository::class);
    $this->service = new MypageService($this->reserveRangeRepo);
    Carbon::setTestNow('2025-10-25 12:00:00'); // 土曜日
  }

  #[Test]
  public function getMyReservation_can_change_format_with_shower_time(): void
  {
    
    $this->reserveRangeRepo->method('getByUserId')->willReturn([
      [
        'reserve_date' => '2025-10-26', // 日曜日
        'start_time' => '12:00:00',
        'end_time' => '13:30:00',
        'shower_time' => '13:30:00'
      ],
      [
        'reserve_date' => '2025-10-27', // 月曜日
        'start_time' => '13:00:00',
        'end_time' => '15:00:00',
        'shower_time' => null
      ]
    ]);

    $res = $this->service->getMyReservation(1);

    $this->assertCount(2, $res['reservations']);
    $this->assertSame('10月25日(土)', $res['today']);
    $this->assertSame('13:00', $res['expired']);

    $this->assertSame('10月26日(日)', $res['reservations'][0]['reserve_date']);
    $this->assertSame('12:00', $res['reservations'][0]['start_time']);
    $this->assertSame('13:30', $res['reservations'][0]['end_time']);
    $this->assertSame('13:30', $res['reservations'][0]['shower_time']);
    
    $this->assertSame('10月27日(月)', $res['reservations'][1]['reserve_date']);
    $this->assertSame('13:00', $res['reservations'][1]['start_time']);
    $this->assertSame('15:00', $res['reservations'][1]['end_time']);
    $this->assertNull($res['reservations'][1]['shower_time']);
  }

  #[Test]
  public function getMyReservation_returns_empty_when_no_data(): void
  {
    $this->reserveRangeRepo->method('getByUserId')->willReturn([]);
    $res = $this->service->getMyReservation(1);

    $this->assertSame([], $res['reservations']);
    $this->assertSame('10月25日(土)', $res['today']);
    $this->assertSame('13:00', $res['expired']);
  }

}