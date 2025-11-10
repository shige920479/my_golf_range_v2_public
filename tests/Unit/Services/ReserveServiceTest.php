<?php

use App\Database\DbConnect;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ServerErrorException;
use App\Repositories\FeeRepository;
use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ReserveRangeRepository;
use App\Repositories\ReserveRentalRepository;
use App\Repositories\ReserveShowerRepository;
use App\Repositories\ShowerRepository;
use App\Services\User\ReserveFormService;
use App\Services\User\ReserveService;
use App\Services\User\ReserveTableService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReserveServiceTest extends TestCase
{
  private ReserveService $service;
  /** @var MockObject&RangeRepository */
  private $rangeRepo;
  /** @var MockObject&RentalRepository */
  private $rentalRepo;
  private $showerRepo;
  /** @var MockObject&ReserveRangeRepository */
  private $reserveRangeRepo;
  /** @var MockObject&ReserveRentalRepository */
  private $reserveRentalRepo;
  /** @var MockObject&ReserveShowerRepository */
  private $reserveShowerRepo;
  private $formService;
  private $tableService;
  private $feeRepo;
  /** @var MockObject&DbConnect */
  private $db;
  

  protected function setUp(): void
  {
    $this->rangeRepo = $this->createMock(RangeRepository::class);
    $this->rentalRepo = $this->createMock(RentalRepository::class);
    $this->showerRepo = $this->createMock(ShowerRepository::class);
    $this->reserveRangeRepo = $this->createMock(ReserveRangeRepository::class);
    $this->reserveRentalRepo = $this->createMock(ReserveRentalRepository::class);
    $this->reserveShowerRepo = $this->createMock(ReserveShowerRepository::class);
    $this->formService = $this->createMock(ReserveFormService::class);
    $this->tableService = $this->createMock(ReserveTableService::class);
    $this->feeRepo = $this->createMock(FeeRepository::class);
    $this->db = $this->createMock(DbConnect::class);

    $this->service = new ReserveService(
      $this->rangeRepo,
      $this->rentalRepo,
      $this->showerRepo,
      $this->reserveRangeRepo,
      $this->reserveRentalRepo,
      $this->reserveShowerRepo,
      $this->formService,
      $this->tableService,
      $this->feeRepo,
      $this->db,
    );

    if(session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    } 
  }

  protected function tearDown(): void
  {
    if(session_status() === PHP_SESSION_ACTIVE) {
      $_SESSION = [];
      session_destroy();
    }
  }

  #[Test]
  public function getReservePageData_returns_expected_structure(): void
  {
    $this->reserveRangeRepo->method('getRangeReservation')->willReturn([['dummy' => 1]]);
    $this->reserveRentalRepo->method('unionRentalAndShowerReservation')->willReturn([['dummy' => 2]]);
    $this->formService->method('generateTime')->willReturn([['value' => '08:00:00', 'label' => '08:00']]);
    $this->formService->method('getFormData')->willReturn(['form' => 'ok']);
    $this->formService->method('availableDate')->willReturn([['value' => '2025-10-09']]);
    $this->rentalRepo->method('get')->willReturn([['rental' => 'test']]);
    $this->tableService->method('getMatrix')->willReturn(['matrix' => 'ok']);
    $_SERVER['REQUEST_URI'] = '/test/test123';

    $res = $this->service->getReservePageData('2025-10-09', 1);

    $this->assertArrayHasKey('form', $res);
    $this->assertArrayHasKey('reservableDates', $res);
    $this->assertArrayHasKey('startTimes', $res);
    $this->assertArrayHasKey('endTimes', $res);
    $this->assertArrayHasKey('rentals', $res);
    $this->assertArrayHasKey('searchDate', $res);
    $this->assertArrayHasKey('calenderTimes', $res);
    $this->assertArrayHasKey('rangeMatrix', $res);
    $this->assertArrayHasKey('optionMatrix', $res);
    $this->assertArrayHasKey('backUrl', $res);
    $this->assertSame('2025-10-09', $res['searchDate']);

    $_SERVER = [];
  }

  #[Test]
  public function createReservation_can_build_reservation(): void
  {
    $req = [
      'no_require1' => 'test',
      'no_require2' => 'test',
      'range_id' => 1,
      'number' => 2,
      'reserve_date' => '2025-10-12',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'rental' => 1,
      'rental_id' => 2,
      'shower' => 1,
      'shower_time' => '09:30:00',
      'usage_time' => 1,
      'back_url' => '/reservation/?search_date=20251012'
    ];

    $fee = [
      'entrance_fee' => '800',
      'range_hourly_fee' => '1000',
      'rental_fee' => '500',
      'shower_fee' => '300'
    ];

    $res = $this->service->createReservation($req, $fee);

    $this->assertArrayNotHasKey('no_require1', $res);
    $this->assertArrayNotHasKey('no_require2', $res);
    $this->assertEquals([
      'range_id' => 1,
      'number' => 2,
      'reserve_date' => '2025-10-12',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'entrance_fee' => '800',
      'range_hourly_fee' => '1000',
      'rental' => 1,
      'rental_id' => 2,
      'rental_fee' => '500',
      'shower' => 1,
      'shower_time' => '09:30:00',
      'shower_fee' => '300',
      'usage_time' => 1,
      'back_url' => '/reservation/?search_date=20251012',
    ], $res);
  }
  #[Test]
  public function createReservation_contain_empty_keys(): void
  {
    $req = [
      'no_require1' => 'test',
      'no_require2' => 'test',
      'range_id' => 1,
      'number' => 2,
      'reserve_date' => '2025-10-12',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      // 'rental' => 1,
      // 'rental_id' => 2,
      // 'shower' => 1,
      // 'shower_time' => '09:30:00'
      'usage_time' => 1,
      'back_url' => 'test/test123'
    ];
    $fee = [
      'entrance_fee' => '800',
      'range_hourly_fee' => '1000',
    ];

    $res = $this->service->createReservation($req, $fee);

    $this->assertEquals([
      'range_id' => 1,
      'number' => 2,
      'reserve_date' => '2025-10-12',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'entrance_fee' => '800',
      'range_hourly_fee' => '1000',
      'rental' => '',
      'rental_id' => '',
      'rental_fee' => '',
      'shower' => '',
      'shower_time' => '',
      'shower_fee' => '',
      'usage_time' => 1,
      'back_url' => 'test/test123'
    ], $res);
  }
  #[Test]
  public function getOtherDisplayData_with_option(): void
  {
    $this->rangeRepo->method('getById')->willReturn(['name' => 'range1']);
    $this->rentalRepo->method('getById')->willReturn(['brand' => 'brand-1', 'model' => 'model-1']);
    $reservation = ['range_id' => 1, 'rental_id' => 1, 'reserve_date' => '2025-10-25'];

    $res = $this->service->getOtherDisplayData($reservation);

    $this->assertSame([
      'format_date' => '10月25日(土)',
      'range_name' => 'range1',
      'brand' => 'brand-1',
      'model' => 'model-1'
    ], $res);
  }

  #[Test]
  public function getOtherDisplayData_with_empty_option(): void
  {
    $this->rangeRepo->method('getById')->willReturn(['name' => 'range1']);
    $this->rentalRepo->expects($this->never())->method('getById');
    $reservation = ['range_id' => 1, 'rental_id' => '',  'reserve_date' => '2025-10-25'];

    $res = $this->service->getOtherDisplayData($reservation);

    $this->assertSame([
      'format_date' => '10月25日(土)',
      'range_name' => 'range1',
      'brand' => '',
      'model' => ''
    ], $res);
  }

  #[Test]
  public function storeReservation_with_all_option(): void
  {
    $reservation = [
      'entrance_fee' => 1000,
      'range_hourly_fee' => 2000,
      'rental' => 1,
      'shower' => 1,
      'shower_time' => '13:00:00'
    ];

    $this->db->expects($this->once())->method('beginTransaction');
    $this->reserveRangeRepo->expects($this->once())->method('store');
    $this->db->expects($this->once())->method('lastInsertId')->willReturn(1);
    $this->reserveRentalRepo->expects($this->once())->method('store');
    $this->reserveShowerRepo->expects($this->once())->method('store');
    $this->db->expects($this->once())->method('commit');
    $this->db->expects($this->never())->method('rollBack');

    $this->service->storeReservation(1, $reservation);
  }
  #[Test]
  public function storeReservation_with_option_only_rental(): void
  {
    $reservation = [
      'entrance_fee' => 1000,
      'range_hourly_fee' => 2000,
      'rental' => 1,
      'shower' => '',
      'shower_time' => ''
    ];

    $this->db->expects($this->once())->method('beginTransaction');
    $this->reserveRangeRepo->expects($this->once())->method('store');
    $this->db->expects($this->once())->method('lastInsertId')->willReturn(1);
    $this->reserveRentalRepo->expects($this->once())->method('store');
    $this->reserveShowerRepo->expects($this->never())->method('store');
    $this->db->expects($this->once())->method('commit');
    $this->db->expects($this->never())->method('rollBack');

    $this->service->storeReservation(1, $reservation);
  }
  #[Test]
  public function storeReservation_with_option_only_shower(): void
  {
    $reservation = [
      'entrance_fee' => 1000,
      'range_hourly_fee' => 2000,
      'rental' => '',
      'shower' => 1,
      'shower_time' => '13:00:00'
    ];

    $this->db->expects($this->once())->method('beginTransaction');
    $this->reserveRangeRepo->expects($this->once())->method('store');
    $this->db->expects($this->once())->method('lastInsertId')->willReturn(1);
    $this->reserveRentalRepo->expects($this->never())->method('store');
    $this->reserveShowerRepo->expects($this->once())->method('store');
    $this->db->expects($this->once())->method('commit');
    $this->db->expects($this->never())->method('rollBack');

    $this->service->storeReservation(1, $reservation);
  }
  #[Test]
  public function storeReservation_with_exception_expects_rollback(): void
  {
    $reservation = [
      'entrance_fee' => 1000,
      'range_hourly_fee' => 2000,
      'rental' => 1,
      'shower' => 1,
      'shower_time' => '13:00:00'
    ];

    $this->db->expects($this->once())->method('beginTransaction');
    $this->reserveRangeRepo->expects($this->once())->method('store');

    $this->db->expects($this->once())->method('lastInsertId')
      ->willThrowException(new Exception('DB error'));

    $this->reserveRentalRepo->expects($this->never())->method('store');
    $this->reserveShowerRepo->expects($this->never())->method('store');
    $this->db->expects($this->never())->method('commit');
    $this->db->expects($this->once())->method('rollBack');

    $this->expectException(ServerErrorException::class);
    $this->service->storeReservation(1, $reservation);
  }

  #[Test]
  public function getReservationById_returns_null_when_not_exists_data(): void
  {
    $this->reserveRangeRepo->method('getById')->willReturn(false);

    $res = $this->service->getReservationById(1);

    $this->assertNull($res);
  }

  #[Test]
  public function updateReservation_update_success_with_all_option(): void
  {
    $id = 1;
    $_SESSION['user']['id'] = 1;
    $reservation = [
      'user_id' => 1,
      'entrance_fee' => 1000,
      'range_hourly_fee' => 1500,
      'rental' => 1,
      'shower' => 1,
      'shower_time' => '13:00:00'
    ];

    $this->db->expects($this->once())->method('beginTransaction');

    $this->reserveRangeRepo->expects($this->once())->method('update')
      ->with(
        $this->callback(function ($id) {
          return $id === 1;
        }),
        $this->callback(function ($reservation) {
          return $reservation['range_fee'] === 2500
          && $reservation['shower_end'] === '13:30:00';
        }));
    $this->reserveRentalRepo->expects($this->once())->method('exists')->willReturn(true);
    $this->reserveRentalRepo->expects($this->once())->method('update');
    $this->reserveRentalRepo->expects($this->never())->method('store');
    $this->reserveRentalRepo->expects($this->never())->method('cancel');

    $this->reserveShowerRepo->expects($this->once())->method('exists')->willReturn(true);
    $this->reserveShowerRepo->expects($this->once())->method('update');
    $this->reserveShowerRepo->expects($this->never())->method('store');
    $this->reserveShowerRepo->expects($this->never())->method('cancel');

    $this->db->expects($this->once())->method('commit');
    $this->db->expects($this->never())->method('rollBack');

    $this->service->updateReservation($id, $reservation);
  }

  #[Test]
  public function updateReservation_update_success_with_add_options(): void
  {
    $id = 1;
    $_SESSION['user']['id'] = 1;
    $reservation = [
      'user_id' => 1,
      'entrance_fee' => 1000,
      'range_hourly_fee' => 1500,
      'rental' => 1,
      'shower' => 1,
      'shower_time' => '13:00:00'
    ];

    $this->db->expects($this->once())->method('beginTransaction');

    $this->reserveRangeRepo->expects($this->once())->method('update');
    $this->reserveRentalRepo->expects($this->once())->method('exists')->willReturn(false);
    $this->reserveRentalRepo->expects($this->never())->method('update');
    $this->reserveRentalRepo->expects($this->once())->method('store');
    $this->reserveRentalRepo->expects($this->never())->method('cancel');

    $this->reserveShowerRepo->expects($this->once())->method('exists')->willReturn(false);
    $this->reserveShowerRepo->expects($this->never())->method('update');
    $this->reserveShowerRepo->expects($this->once())->method('store');
    $this->reserveShowerRepo->expects($this->never())->method('cancel');

    $this->db->expects($this->once())->method('commit');
    $this->db->expects($this->never())->method('rollBack');

    $this->service->updateReservation($id, $reservation);
  }
  #[Test]
  public function updateReservation_update_success_with_cancels_options(): void
  {
    $id = 1;
    $_SESSION['user']['id'] = 1;
    $reservation = [
      'user_id' => 1,
      'entrance_fee' => 1000,
      'range_hourly_fee' => 1500,
      'rental' => '',
      'shower' => '',
      'shower_time' => ''
    ];

    $this->db->expects($this->once())->method('beginTransaction');

    $this->reserveRangeRepo->expects($this->once())->method('update');
    $this->reserveRentalRepo->expects($this->once())->method('exists')->willReturn(true);
    $this->reserveRentalRepo->expects($this->never())->method('update');
    $this->reserveRentalRepo->expects($this->never())->method('store');
    $this->reserveRentalRepo->expects($this->once())->method('cancel');

    $this->reserveShowerRepo->expects($this->once())->method('exists')->willReturn(true);
    $this->reserveShowerRepo->expects($this->never())->method('update');
    $this->reserveShowerRepo->expects($this->never())->method('store');
    $this->reserveShowerRepo->expects($this->once())->method('cancel');

    $this->db->expects($this->once())->method('commit');
    $this->db->expects($this->never())->method('rollBack');

    $this->service->updateReservation($id, $reservation);
  }

  #[Test]
  public function updateReservation_call_rollback_and_exception_when_failed(): void
  {
    $id = 1;
    $_SESSION['user']['id'] = 1;
    $reservation = [
      'user_id' => 1,
      'entrance_fee' => 1000,
      'range_hourly_fee' => 1500,
      'rental' => 1,
      'shower' => 1,
      'shower_time' => '13:00:00'
    ];

    $this->db->expects($this->once())->method('beginTransaction');

    $this->reserveRangeRepo->expects($this->once())->method('update');
    $this->reserveRentalRepo->expects($this->once())->method('exists')->willReturn(true);
    $this->reserveRentalRepo->expects($this->once())->method('update');
    $this->reserveRentalRepo->expects($this->never())->method('store');
    $this->reserveRentalRepo->expects($this->never())->method('cancel');

    $this->reserveShowerRepo->expects($this->once())->method('exists')->willReturn(true);
    $this->reserveShowerRepo->expects($this->once())->method('update')->willThrowException(new Exception('db-error'));
    $this->reserveShowerRepo->expects($this->never())->method('store');
    $this->reserveShowerRepo->expects($this->never())->method('cancel');

    $this->db->expects($this->never())->method('commit');
    $this->db->expects($this->atLeastOnce())->method('rollBack');

    $this->expectException(ServerErrorException::class);
    $this->service->updateReservation($id, $reservation);
  }

  #[Test]
  public function cancelReservation_rollbacks_on_error(): void
  {
    $id = 1;
    $this->reserveRangeRepo->expects($this->once())->method('cancel')
      ->willThrowException(new Exception('db-error'));
    $this->db->expects($this->atLeastOnce())->method('rollBack');

    $this->expectException(ServerErrorException::class);
    $this->service->cancelReservation($id);
  }

  #[Test]
  public function checkOwner_passes_when_owner(): void
  {
    $_SESSION['user']['id'] = 1;
    $this->reserveRangeRepo->method('isOwnedByUser')->willReturn(true);

    $this->service->checkOwner(99);
    $this->assertTrue(true);
  }

  #[Test]
  public function checkOwner_throws_exception_when_not_owner(): void
  {
    $id = 99;
    $_SESSION['user']['id'] = 1;
    $this->reserveRangeRepo->method('isOwnedByUser')->willReturn(false);

    $this->expectException(ForbiddenException::class);
    $this->expectExceptionMessage("アクセス権限がありません。予約id：{$id} / ユーザーid：1");
    $this->service->checkOwner($id);
  }

}
