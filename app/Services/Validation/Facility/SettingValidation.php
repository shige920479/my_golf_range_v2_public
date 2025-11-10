<?php
namespace App\Services\Validation\Facility;

use App\Services\Validation\BaseValidation;
use App\Services\Validation\FormRulesTrait;
use Carbon\Carbon;

class SettingValidation extends BaseValidation
{
  use FormRulesTrait;

  public function rangeFeeValidation(array $request): bool
  {
    if ($this->required('entrance_fee', $request['entrance_fee'] ?? '')) {
      $this->integer('entrance_fee', $request['entrance_fee']);
    }
    if ($this->required('weekday_fee', $request['weekday_fee'] ?? '')) {
      $this->integer('weekday_fee', $request['weekday_fee']);
    }
    if ($this->required('holiday_fee', $request['holiday_fee'] ?? '')) {
      $this->integer('holiday_fee', $request['holiday_fee']);
    }
    if ($this->required('effective_date', $request['effective_date'] ?? '')) {
      if ($this->dateForm('effective_date', $request['effective_date'])) {
        $this->isRevisional($request['effective_date']);
      }
    }
    
    return empty($this->getErrors());
  }

  public function rentalFeeValidation(array $request): bool
  {
    if ($this->required('rental_fee', $request['rental_fee'] ?? '')) {
      $this->integer('rental_fee', $request['rental_fee']);
    }
    if ($this->required('effective_date', $request['effective_date'] ?? '')) {
      if($this->dateForm('effective_date', $request['effective_date'])) {
        $this->isRevisional($request['effective_date']);
      }
    }
    
    return empty($this->getErrors());
  }

  public function showerFeeValidation(array $request): bool
  {
    if ($this->required('shower_fee', $request['shower_fee'] ?? '')) {
      $this->integer('shower_fee', $request['shower_fee']);
    }
    if ($this->required('effective_date', $request['effective_date'] ?? '')) {
      if($this->dateForm('effective_date', $request['effective_date'])) {
        $this->isRevisional($request['effective_date']);
      }
    }
    
    return empty($this->getErrors());
  }

  public function mainteDateValidation(array $request): bool
  {
    if ($this->required('mainte_date', $request['mainte_date'] ?? '')) {
      if($this->dateForm('mainte_date', $request['mainte_date'])) {
        $this->isRevisional($request['mainte_date'], 'mainte_date');
      }
    }
    
    return empty($this->getErrors());
  }

  /** 改定日が3週間先以降か */
  private function isRevisional(string $date, string $key = 'effective_date'): void
  {
    if (Carbon::parse($date) < Carbon::today()->addDays(MAX_RESERVE_DATE)) {
      $this->errors[$key] = '3週間先以降で設定願います';
    }
  }
}