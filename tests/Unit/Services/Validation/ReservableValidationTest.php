<?php

use App\Repositories\ReserveRangeRepository;
use App\Repositories\ReserveRentalRepository;
use App\Repositories\ReserveShowerRepository;
use App\Services\Validation\Reserve\ReservableValidation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReservableValidationTest extends TestCase
{
  /** @var MockObject&ReserveRangeRepository */
  private ReserveRangeRepository $rangeRepo;
  /** @var MockObject&ReserveRentalRepository */
  private ReserveRentalRepository $rentalRepo;
  /** @var MockObject&ReserveShowerRepository */
  private ReserveShowerRepository $showerRepo;
  private ReservableValidation $validator;
  private array $req;

  protected function setUp(): void
  {
    $this->rangeRepo = $this->createMock(ReserveRangeRepository::class);
    $this->rentalRepo = $this->createMock(ReserveRentalRepository::class);
    $this->showerRepo = $this->createMock(ReserveShowerRepository::class);

    $this->validator = new ReservableValidation($this->rangeRepo, $this->rentalRepo, $this->showerRepo);

    if(session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    $_SESSION['user']['id'] = 1;

    $this->req = [
      'range_id' => 1,
      'reserve_date' => '2025-10-10',
      'start_time' => '12:00:00',
      'end_time' => '13:00:00',
      'rental' => 1,
      'rental_id' => 1,
      'shower' => 1,
      'shower_time' => '13:00:00',
    ];
  }

  protected function tearDown(): void
  {
    $_SESSION = [];
    if(session_status() === PHP_SESSION_ACTIVE) {
      session_destroy();
    }
  }

  #[Test]
  public function validate_with_valid_request(): void
  {
    $this->rangeRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);
    $this->rentalRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);
    $this->showerRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);

    $res = $this->validator->validate($this->req);

    $this->assertTrue($res);
    $this->assertArrayNotHasKey('start_time', $this->validator->getErrors());
    $this->assertArrayNotHasKey('rental_id', $this->validator->getErrors());
    $this->assertArrayNotHasKey('shower_time', $this->validator->getErrors());
  }
  
  #[Test]
  public function validate_with_duplicated_reserve_range(): void
  {
    $this->rangeRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(false);
    $this->rentalRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);
    $this->showerRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);

    $res = $this->validator->validate($this->req);

    $this->assertFalse($res);
    $this->assertArrayHasKey('start_time', $this->validator->getErrors());
    $this->assertSame("既に予約があります,他の時間帯を選択願います", $this->validator->getErrors()['start_time']);
  }

  #[Test]
  public function validate_with_duplicated_reserve_rental(): void
  {
    $this->rangeRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);
    $this->rentalRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(false);
    $this->showerRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);

    $res = $this->validator->validate($this->req);

    $this->assertFalse($res);
    $this->assertArrayHasKey('rental_id', $this->validator->getErrors());
    $this->assertSame("既に予約があります", $this->validator->getErrors()['rental_id']);

  }
  #[Test]
  public function validate_with_duplicated_reserve_shower(): void
  {
    $this->rangeRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);
    $this->rentalRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);
    $this->showerRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(false);

    $res = $this->validator->validate($this->req);

    $this->assertFalse($res);
    $this->assertArrayHasKey('shower_time', $this->validator->getErrors());
    $this->assertSame("既に予約があります", $this->validator->getErrors()['shower_time']);
  }

  #[Test]
  public function validate_with_valid_req_when_empty_option(): void
  {
    $this->rangeRepo->expects($this->once())->method('isReservableTime')
      ->willReturn(true);
    $this->rentalRepo->expects($this->never())->method('isReservableTime');
    $this->showerRepo->expects($this->never())->method('isReservableTime');

    $req = [
      'range_id' => 1,
      'reserve_date' => '2025-10-10',
      'start_time' => '12:00:00',
      'end_time' => '13:00:00',
    ];

    $res = $this->validator->validate($req);

    $this->assertTrue($res);
  }


}