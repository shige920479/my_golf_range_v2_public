<?php
namespace App\Services\User;

use App\Repositories\FeeRepository;
use Carbon\Carbon;

class CalcFeeService
{
  public function __construct(
    private FeeRepository $feeRepo,
  )
  {
  }

/**
 * @return array{
 *   entrance_fee:int,
 *   range_hourly_fee:int,
 *   rental_fee:int,
 *   shower_fee:int,
 *   usage_time:float
 * }
 */
  public function calcFee(array $request): array
  {
    $number = (int)$request['number'];
    $date = $request['reserve_date'];
    $startTime = Carbon::createFromTimeString($request['start_time']);
    $endTime = Carbon::createFromTimeString($request['end_time']);
    $usageTime = $startTime->diffInMinutes($endTime) / 30 * 0.5;
    $rental = $request['rental'] ?? '';
    $shower = $request['shower'] ?? '';
    $showerTime = $request['shower_time'] ?? '';

    $rangeSetting = $this->feeRepo->getFee('rangeFee', $date);

    $entranceFee = (int)($rangeSetting['entrance_fee'] * $number);

    if(Carbon::parse($date)->isWeekday()) {
      $rangeHourlyFee = (int)($rangeSetting['weekday_fee'] * $usageTime);
    } else {
      $rangeHourlyFee = (int)($rangeSetting['holiday_fee'] * $usageTime);
    }

    if(! empty($rental)) {
      $rentalSetting = $this->feeRepo->getFee('rentalFee', $date);
      $rentalFee = (int)$rentalSetting['rental_fee'] * $usageTime;
    } else {
      $rentalFee = 0;
    }

    if(! empty($shower) && $showerTime !== '') {
      $showerFee = (int)$this->feeRepo->getFee('showerFee', $date)['shower_fee'];
    } else {
      $showerFee = 0;
    }

    return [
      'entrance_fee' => (int)$entranceFee,
      'range_hourly_fee' => (int)$rangeHourlyFee,
      'rental_fee' => (int)$rentalFee,
      'shower_fee' => (int)$showerFee,
      'usage_time' => $usageTime
    ];
  }

}