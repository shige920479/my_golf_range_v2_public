<?php

use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ShowerRepository;
use App\Services\User\ReserveFormService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

class ReserveFormServiceTest extends TestCase
{
  /** @var Mockobject&RangeRepository */
  private RangeRepository $rangeRepo;
  /** @var Mockobject&RentalRepository */
  private RentalRepository $rentalRepo;
  /** @var Mockobject&ShowerRepository */
  private ShowerRepository $showerRepo;
  private ReserveFormService $service;

  protected function setUp(): void
  {
    $this->rangeRepo = $this->createMock(RangeRepository::class);
    $this->rentalRepo = $this->createMock(RentalRepository::class);
    $this->showerRepo = $this->createMock(ShowerRepository::class);
    $this->service = new ReserveFormService($this->rangeRepo, $this->rentalRepo, $this->showerRepo);
  }

  #[Test]
  public function getFormData_returns_facilities():void
  {
    $this->rangeRepo->method('get')->willReturn([
      ['name' => 'range1', 'mainte_date' => '2025-01-01']
    ]);
    $this->rentalRepo->method('get')->willReturn([
      ['brand' => 'testbrand', 'model' => 'testmodel', 'mainte_date' => '2025-02-02']
    ]);
    $this->showerRepo->method('get')->willReturn([
      ['mainte_date' => '2025-03-03']
    ]);

    $res = $this->service->getFormData();

    $this->assertSame(
      [
        'range' => [['name' => 'range1', 'mainte_date' => '2025-01-01']],
        'rental'  => [['brand' => 'testbrand', 'model' => 'testmodel', 'mainte_date' => '2025-02-02']],
        'shower'  => [['mainte_date' => '2025-03-03']]
      ], $res
    );
  }

  #[Test]
  public function availableDate_generates_expected_number_of_days():void
  {
    Carbon::setTestNow('2025-01-01');

    $res = $this->service->availableDate();

    $this->assertCount(3, $res);
    $this->assertSame([
        ['value' => '2025-01-01', 'label' => '01月01日(水)'],
        ['value' => '2025-01-02', 'label' => '01月02日(木)'],
        ['value' => '2025-01-03', 'label' => '01月03日(金)'],
    ], $res);
  }

  #[Test]
  public function generateTime_returns_time_slots_every_30_minutes(): void
  {
    $res = $this->service->generateTime(0, 30);

    $this->assertSame([
      ['value' => '08:00:00', 'label' => '08:00'],
      ['value' => '08:30:00', 'label' => '08:30'],
      ['value' => '09:00:00', 'label' => '09:00'],
    ], $res);

  }
  #[Test]
  public function generateTime_returns_time_slots_adjust_30_minutes(): void
  {
    $res = $this->service->generateTime(30, 30);

    $this->assertSame([
      ['value' => '08:30:00', 'label' => '08:30'],
      ['value' => '09:00:00', 'label' => '09:00'],
      ['value' => '09:30:00', 'label' => '09:30'],
    ], $res);
  }
}