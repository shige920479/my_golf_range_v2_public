<?php

use App\Exceptions\BadRequestException;
use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ShowerRepository;
use App\Services\Validation\Reserve\FacilityValidation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FacilityValidationTest extends TestCase
{
  /** @var MockObject&RangeRepository */
  private RangeRepository $rangeRepo;
  /** @var MockObject&RentalRepository */
  private RentalRepository $rentalRepo;
  /** @var MockObject&ShowerRepository */
  private ShowerRepository $showerRepo;
  private FacilityValidation $validator;
  private array $request;

  protected function setUp(): void
  {
    $this->rangeRepo = $this->createMock(RangeRepository::class);
    $this->rentalRepo = $this->createMock(RentalRepository::class);
    $this->showerRepo = $this->createMock(ShowerRepository::class);
    $this->validator = new FacilityValidation($this->rangeRepo, $this->rentalRepo, $this->showerRepo);

    $this->request = [
      'range_id' => 1,
      'reserve_date' => '2025-10-10',
      'rental' => 1,
      'rental_id' => 1,
      'shower' => 1,
    ];
  }

  #[Test]
  public function existsFacility_exist_returns_true(): void
  {
    $req = ['range_id' => 1, 'rental_id' => 2];

    $this->rangeRepo->method('exists')->willReturn(true);
    $this->rentalRepo->method('exists')->willReturn(true);
    

    $res = $this->validator->existsFacility($req);

    $this->assertTrue($res);
  }

  #[Test]
  public function existsFacility_exception_if_invalid_range_id(): void
  {
    $req = ['range_id' => 1, 'rental_id' => 2];

    $this->rangeRepo->method('exists')->willReturn(false);
    $this->rentalRepo->expects($this->never())->method('exists');

    $this->expectException(BadRequestException::class);
    $res = $this->validator->existsFacility($req);
  }

  #[Test]
  public function existsFacility_exception_if_invalid_rental_id(): void
  {
    $req = ['range_id' => 1, 'rental_id' => 2];

    $this->rangeRepo->expects($this->once())->method('exists')->willReturn(true);
    $this->rentalRepo->method('exists')->willReturn(false);

    $this->expectException(BadRequestException::class);
    $res = $this->validator->existsFacility($req);
  }

  #[Test]
  public function existsFacility_exception_if_null_range_id(): void
  {
    $req = ['range_id' => '', 'rental_id' => 2];

    $this->expectException(BadRequestException::class);
    $res = $this->validator->existsFacility($req);
  }

  #[Test]
  public function isAvailable_with_valid_request(): void
  {
    $this->rangeRepo->expects($this->once())->method('isAvailableDate')->willReturn(true);
    $this->rentalRepo->expects($this->once())->method('isAvailableDate')->willReturn(true);
    $this->showerRepo->expects($this->once())->method('isAvailableDate')->willReturn(true);

    $res = $this->validator->isAvailable($this->request);

    $this->assertTrue($res);
    $this->assertArrayNotHasKey('range_id', $this->validator->getErrors());
    $this->assertArrayNotHasKey('rental_id', $this->validator->getErrors());
    $this->assertArrayNotHasKey('shower_time', $this->validator->getErrors());
  }
  
  #[Test]
  public function isAvailable_with_duplicated_reserve_range(): void
  {
    $this->rangeRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(false);
    $this->rentalRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(true);
    $this->showerRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(true);

    $res = $this->validator->isAvailable($this->request);

    $this->assertFalse($res);
    $this->assertArrayHasKey('range_id', $this->validator->getErrors());
    $this->assertSame("メンテナンス日の為、再選択願います", $this->validator->getErrors()['range_id']);
  }

  #[Test]
  public function isAvailable_with_duplicated_reserve_rental(): void
  {
    $this->rangeRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(true);
    $this->rentalRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(false);
    $this->showerRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(true);

    $res = $this->validator->isAvailable($this->request);

    $this->assertFalse($res);
    $this->assertArrayHasKey('rental_id', $this->validator->getErrors());
    $this->assertSame("メンテナンス日の為、再選択願います", $this->validator->getErrors()['rental_id']);

  }
  #[Test]
  public function isAvailable_with_duplicated_reserve_shower(): void
  {
    $this->rangeRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(true);
    $this->rentalRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(true);
    $this->showerRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(false);

    $res = $this->validator->isAvailable($this->request);

    $this->assertFalse($res);
    $this->assertArrayHasKey('shower_time', $this->validator->getErrors());
    $this->assertSame("メンテナンス日の為、再選択願います", $this->validator->getErrors()['shower_time']);
  }

  #[Test]
  public function isAvailable_with_valid_req_when_empty_option(): void
  {
    $this->rangeRepo->expects($this->once())->method('isAvailableDate')
      ->willReturn(true);
    $this->rentalRepo->expects($this->never())->method('isAvailableDate');
    $this->showerRepo->expects($this->never())->method('isAvailableDate');

    $req = [
      'range_id' => 1,
      'reserve_date' => '2025-10-10',
    ];

    $res = $this->validator->isAvailable($req);

    $this->assertTrue($res);
  }
}