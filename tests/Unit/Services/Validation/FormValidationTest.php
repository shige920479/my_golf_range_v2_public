<?php

use App\Services\Validation\Reserve\FormValidation;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FormValidationTest extends TestCase
{
  private FormValidation $validator;

  protected function setUp(): void
  {
    $this->validator = new FormValidation();
  }

  #[Test]
  public function validate_returns_true_when_all_fields_are_valid(): void
  {
    $request = [
      'range_id'     => '1',
      'number'       => '2',
      'reserve_date' => '2025-10-10',
      'start_time'   => '09:00:00',
      'end_time'     => '10:00:00',
      'rental'       => '1',
      'rental_id'    => '5',
      'shower'       => '1',
      'shower_time'  => '11:00:00',
    ];

    $res = $this->validator->validate($request);

    $this->assertTrue($res);
    $this->assertSame([], $this->validator->getErrors());
  }

  #[Test]
  public function validate_returns_false_when_required_fields_missing(): void
  {
    $request = [];

    $res = $this->validator->validate($request);

    $this->assertFalse($res);
    $errors = $this->validator->getErrors();

    $this->assertArrayHasKey('range_id', $errors);
    $this->assertArrayHasKey('number', $errors);
    $this->assertArrayHasKey('reserve_date', $errors);
    $this->assertArrayHasKey('start_time', $errors);
    $this->assertArrayHasKey('end_time', $errors);
  }

  #[Test]
  public function validate_returns_false_when_rental_id_without_checkbox(): void
  {
    $request = [
      'range_id' => '1',
      'number' => '1',
      'reserve_date' => '2025-10-10',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'rental' => '', // チェックなし
      'rental_id' => '2', // IDだけ
    ];

    $res = $this->validator->validate($request);

    $this->assertFalse($res);
    $this->assertArrayHasKey('rental', $this->validator->getErrors());
  }

  #[Test]
  public function validate_returns_false_when_rental_checked_but_no_id(): void
  {
    $request = [
      'range_id' => '1',
      'number' => '1',
      'reserve_date' => '2025-10-10',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'rental' => '1', // チェックあり
      'rental_id' => '', // IDなし
    ];

    $res = $this->validator->validate($request);

    $this->assertFalse($res);
    $this->assertArrayHasKey('rental_id', $this->validator->getErrors());
  }

  #[Test]
  public function validate_returns_false_when_shower_time_without_checkbox(): void
  {
    $request = [
      'range_id' => '1',
      'number' => '1',
      'reserve_date' => '2025-10-10',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'shower' => '', // チェックなし
      'shower_time' => '10:00:00', // 時間だけ
    ];

    $res = $this->validator->validate($request);

    $this->assertFalse($res);
    $this->assertArrayHasKey('shower', $this->validator->getErrors());
  }

  #[Test]
  public function validate_returns_false_when_shower_checked_but_no_time(): void
  {
    $request = [
      'range_id' => '1',
      'number' => '1',
      'reserve_date' => '2025-10-10',
      'start_time' => '08:00:00',
      'end_time' => '09:00:00',
      'shower' => '1',
      'shower_time' => '',
    ];

    $res = $this->validator->validate($request);

    $this->assertFalse($res);
    $this->assertArrayHasKey('shower_time', $this->validator->getErrors());
  }

  #[Test]
  public function validate_returns_false_when_invalid_date_or_time_format(): void
  {
    $request = [
      'range_id' => '1',
      'number' => '2',
      'reserve_date' => 'invalid-date',
      'start_time' => '25:00:00', // 存在しない時刻
      'end_time' => '99:99:99',   // 存在しない時刻
    ];

    $res = $this->validator->validate($request);

    $this->assertFalse($res);
    $errors = $this->validator->getErrors();

    $this->assertArrayHasKey('reserve_date', $errors);
    $this->assertArrayHasKey('start_time', $errors);
    $this->assertArrayHasKey('end_time', $errors);
  }
}