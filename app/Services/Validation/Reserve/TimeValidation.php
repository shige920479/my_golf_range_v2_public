<?php
namespace App\Services\Validation\Reserve;

use App\Exceptions\BadRequestException;
use App\Services\Validation\BaseValidation;
use Carbon\Carbon;

class TimeValidation extends BaseValidation
{
  public function validate(array $request): bool
  {
    $reserveDate = Carbon::parse($request['reserve_date']);
    $start = Carbon::parse($request['reserve_date'] . ' ' . $request['start_time']);
    $end = Carbon::parse($request['reserve_date'] . ' ' . $request['end_time']);
    $limit = Carbon::now()->addHour();

    if ($reserveDate->isBefore(Carbon::today())) {
      throw new BadRequestException('過去の日付が入力されました');
    }

    if ($start->isBefore($limit)) {
      $this->errors['start_time'] = '既に予約可能な時間を過ぎています';
      return false;
    }

    if ($end->lessThanOrEqualTo($start)) {
      $this->errors['start_time'] = '終了時間が違っています';
      return false;
    }

    return true;
  }


}