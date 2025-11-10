<?php
namespace App\Services\Validation\Reserve;

use App\Repositories\ReserveRangeRepository;
use App\Repositories\ReserveRentalRepository;
use App\Repositories\ReserveShowerRepository;
use App\Services\Validation\BaseValidation;

class ReservableValidation extends BaseValidation
{

  public function __construct(
    private ReserveRangeRepository $rangeRepo,
    private ReserveRentalRepository $rentalRepo,
    private ReserveShowerRepository $showerRepo,
  )
  {
  }

  public function validate(array $request): bool
  {
    $rangeId = $request['range_id'] ?? '';
    $reserveDate = $request['reserve_date'] ?? '';
    $startTime = $request['start_time'] ?? '';
    $endTime = $request['end_time'] ?? '';
    $rental = $request['rental'] ?? '';
    $rentalId = $request['rental_id'] ?? '';
    $shower = $request['shower'] ?? '';
    $showerTime = $request['shower_time'] ?? '';
    $userId = $_SESSION['user']['id'];
    $method = $request['method'] ?? 'store';

    if (! $this->rangeRepo->isReservableTime($rangeId, $reserveDate, $startTime, $endTime, $userId, $method)) {
      $this->errors['start_time'] = "既に予約があります,他の時間帯を選択願います";
    }

    if ($method === 'store' || $method !== 'update') {
      if($this->rangeRepo->isDuplicateSameTime($rangeId, $reserveDate, $startTime, $endTime, $userId)) {
        $this->errors['start_time'] = "同一時間帯で予約があります,他の時間帯を選択願います";
      }
    }

    if (! empty($rental)) {
      if (! $this->rentalRepo->isReservableTime($rentalId, $reserveDate, $startTime, $endTime, $userId, $method)) {
        $this->errors['rental_id'] = "既に予約があります";
      }
    }
    if (! empty($shower)) {
      if (! $this->showerRepo->isReservableTime($reserveDate, $showerTime, $userId, $method)) {
        $this->errors['shower_time'] = "既に予約があります";
      }
    }

    return empty($this->getErrors());
  }

}