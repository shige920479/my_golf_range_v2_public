<?php
namespace App\Services\Validation\Reserve;

use App\Services\Validation\BaseValidation;
use App\Services\Validation\FormRulesTrait;

class FormValidation extends BaseValidation
{
  use FormRulesTrait;

  public function validate(array $request): bool
  {
    $rangeId = $request['range_id'] ?? '';
    $number = $request['number'] ?? '';
    $reserveDate = $request['reserve_date'] ?? '';
    $startTime = $request['start_time'] ?? '';
    $endTime = $request['end_time'] ?? '';
    $rental = $request['rental'] ?? '';
    $rentalId = $request['rental_id'] ?? '';
    $shower = $request['shower'] ?? '';
    $showerTime = $request['shower_time'] ?? '';

    if ($this->required('range_id', $rangeId)) {
      $this->numeric('range_id', $rangeId);
    }

    if ($this->required('number', $number)) {
      $this->numeric('number', $number);
    }

    if ($this->required('reserve_date', $reserveDate)) {
      $this->dateForm('reserve_date', $reserveDate);
    }

    if ($this->required('start_time', $startTime)) {
      $this->timeForm('start_time', $startTime);
    }

    if ($this->required('end_time', $endTime)) {
      $this->timeForm('end_time', $endTime);
    }

    if ($rental !== '' && $rentalId !== '') {
      $this->validBoolean('rental', $rental);
      $this->numeric('rental_id', $rentalId);
    } elseif ($rental === '' && $rentalId !== '') {
      $this->errors['rental'] = 'チェックを入力してください';
    } elseif ($rental !== '' && $rentalId === '') {
      $this->errors['rental_id'] = 'レンタルクラブを選択してください';
    }

    if ($shower !== '' && $showerTime !== '') {
      $this->validBoolean('shower', $shower);
      $this->timeForm('shower_time', $showerTime);
    } elseif ($shower === '' && $showerTime !== '') {
      $this->errors['shower'] = 'チェックを入力してください';
    } elseif ($shower !== '' && $showerTime === '') {
      $this->errors['shower_time'] = '時間を選択してください';
    }

    return empty($this->getErrors());
  }
}