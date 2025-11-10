<?php
namespace App\Services\Owner;

use App\Exceptions\BadRequestException;
use App\Repositories\FeeRepository;
use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ShowerRepository;

class SettingService
{
  public function __construct(
    private FeeRepository $feeRepo,
    private RangeRepository $rangeRepo,
    private RentalRepository $rentalRepo,
    private ShowerRepository $showerRepo
  )
  {
  }

  public function buildSettingData(string $date): array
  {
    $rangeKeys = ['entrance_fee', 'weekday_fee', 'holiday_fee', 'effective_date'];
    $rentalKeys = ['rental_fee', 'effective_date'];
    $showerKeys = ['shower_fee', 'effective_date'];

    return [
      'currentRangeFee' => $this->feeRepo->getCurrentFee('rangeFee') ?: array_fill_keys_with_null($rangeKeys),
      'featureRangeFee' => $this->feeRepo->getChangeFee('rangeFee') ?: array_fill_keys_with_null($rangeKeys),
      'currentRentalFee' => $this->feeRepo->getCurrentFee('rentalFee') ?: array_fill_keys_with_null($rentalKeys),
      'featureRentalFee' => $this->feeRepo->getChangeFee('rentalFee') ?: array_fill_keys_with_null($rentalKeys),
      'currentShowerFee' => $this->feeRepo->getCurrentFee('showerFee') ?: array_fill_keys_with_null($showerKeys),
      'featureShowerFee' => $this->feeRepo->getChangeFee('showerFee') ?: array_fill_keys_with_null($showerKeys),
      'mainte' => $this->createMainteArray($date)
    ];
  }

  public function getFee(string $table): array
  {
    $fee = [];
    $fee['current'] = $this->feeRepo->getCurrentFee($table) ?: null;
    $fee['feature'] = $this->feeRepo->getChangeFee($table) ?: null;

    return $fee;
  }

  public function store(array $request, string $table): int
  {
    return match ($table) {
      'rangeFee' => $this->feeRepo->storeRangeFee($request),
      'rentalFee' => $this->feeRepo->storeRentalFee($request),
      'showerFee' => $this->feeRepo->storeShowerFee($request),
      default => throw new BadRequestException('不正なリクエストです、初めからやり直して下さい')
    };
  }

  public function getMainteData(string $table, int $id): array
  {
    return match ($table) {
      'drivingRange' => $this->rangeRepo->getById($id),
      'rental' => $this->rentalRepo->getById($id),
      'shower' => $this->showerRepo->getById($id),
    };
  }

  public function updateMainteDate(string $table, int $id, string $date): int
  {
    return match ($table) {
      'drivingRange' => $this->rangeRepo->updateMainte($date, $id),
      'rental' => $this->rentalRepo->updateMainte($date, $id),
      'shower' => $this->showerRepo->updateMainte($date, $id),
      default => throw new BadRequestException('不正なリクエストです、初めからやり直して下さい')
    };
  }

  private function createMainteArray($date): array
  {
    $allData = $this->rangeRepo->getMainte($date);
    $build = [];
    foreach($allData as $data) {
      if (! array_key_exists($data['facility'], $build)) {
        $build[$data['facility']][] = [
          'id' => $data['id'],
          'name' => $data['name'],
          'model' => $data['model'],
          'mainte_date' => $data['mainte_date'],
        ];
      } else {
        array_push($build[$data['facility']], [
          'id' => $data['id'],
          'name' => $data['name'],
          'model' => $data['model'],
          'mainte_date' => $data['mainte_date'],
        ]);
      }
    }
    return $build;
  }
}