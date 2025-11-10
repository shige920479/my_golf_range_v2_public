<?php

use App\Exceptions\BadRequestException;
use App\Services\Validation\Reserve\TimeValidation;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TimeValidationTest extends TestCase
{
  private TimeValidation $validator;

  protected function setUp(): void
  {
    $this->validator = new TimeValidation();
  }

  #[Test]
  #[DataProvider('validRequestProvider')]
  public function validate_valid_time($req): void
  {
    Carbon::setTestNow('2025-10-13 12:00:00');
    $result = $this->validator->validate($req);

    $this->assertTrue($result);
  }

  #[Test]
  #[DataProvider('inValidRequestProvider')]
  public function validate_inValid_time($req): void
  {
    Carbon::setTestNow('2025-10-13 12:00:00');
    $result = $this->validator->validate($req);

    $this->assertFalse($result);
    $this->assertSame('終了時間が違っています', $this->validator->getErrors()['start_time']);
  }
  #[Test]
  public function validate_with_past_date(): void
  {
    Carbon::setTestNow('2025-10-13 12:00:00');
    $req = [];
    $req['reserve_date'] = '2025-10-12';
    $req['start_time'] = '15:00:00';
    $req['end_time'] = '16:00:00';

    $this->expectException(BadRequestException::class);
    $result = $this->validator->validate($req);
  }

  #[Test]
  public function validate_with_over_limit(): void
  {
    Carbon::setTestNow('2025-10-13 12:00:00');
    $req = [];
    $req['reserve_date'] = '2025-10-13';
    $req['start_time'] = '12:59:59';
    $req['end_time'] = '16:00:00';

    $result = $this->validator->validate($req);

    $this->assertFalse($result);
    $this->assertSame('既に予約可能な時間を過ぎています', $this->validator->getErrors()['start_time']);
  }

  public static function validRequestProvider(): array
  {
    return [
      [['reserve_date' => '2025-10-13', 'start_time' => '15:00:00' , 'end_time' => '16:00:00']],
      [['reserve_date' => '2025-10-13', 'start_time' => '13:00:00' , 'end_time' => '14:00:00']],
    ];
  }

  public static function inValidRequestProvider(): array
  {
    return [
      [['reserve_date' => '2025-10-13', 'start_time' => '15:00:00' , 'end_time' => '15:00:00']],
      [['reserve_date' => '2025-10-13', 'start_time' => '15:00:00' , 'end_time' => '14:00:00']],
    ];
  }

}