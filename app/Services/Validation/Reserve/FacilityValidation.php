<?php
namespace App\Services\Validation\Reserve;

use App\Exceptions\BadRequestException;
use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ShowerRepository;
use App\Services\Validation\BaseValidation;

class FacilityValidation extends BaseValidation
{
  public function __construct(
    private RangeRepository $rangeRepo,
    private RentalRepository $rentalRepo,
    private ShowerRepository $showerRepo
  )
  {
  }

  public function existsFacility(array $request): bool
  {
    $rangeId = $request['range_id'] ?? '';
    $rentalId = $request['rental_id'] ?? '';

    if ($rangeId !== '' && ! $this->rangeRepo->exists($rangeId)) {
      throw new BadRequestException('指定されたレンジが存在しません');
    }
    if ($rentalId !== '' && ! $this->rentalRepo->exists($rentalId)) {
      throw new BadRequestException('指定されたクラブが存在しません');
    }
    
    return true;
  }

  public function isAvailable(array $request): bool
  {
    $reserveDate = $request['reserve_date'];
    $rangeId = $request['range_id'] ?? '';
    $rental = $request['rental'] ?? '';
    $rentalId = $request['rental_id'] ?? '';
    $shower = $request['shower'] ?? '';

    if (! $this->rangeRepo->isAvailableDate($rangeId, $reserveDate)) {
      $this->errors['range_id'] = "メンテナンス日の為、再選択願います";
    }

    if (! empty($rental) && ! empty($rentalId)) {
      if (! $this->rentalRepo->isAvailableDate($rentalId, $reserveDate)) {
        $this->errors['rental_id'] = "メンテナンス日の為、再選択願います";
      }
    }

    if (! empty($shower)) {
      if (! $this->showerRepo->isAvailableDate($reserveDate)) {
        $this->errors['shower_time'] = "メンテナンス日の為、再選択願います";
      }
    }

    return empty($this->getErrors());
  }
}