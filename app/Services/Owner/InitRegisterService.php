<?php
namespace App\Services\Owner;

use App\Repositories\FeeRepository;
use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ShowerRepository;

class InitRegisterService
{
  public function __construct(
    private RangeRepository $rangeRepo,
    private RentalRepository $rentalRepo,
    private ShowerRepository $showerRepo,
    private FeeRepository $feeRepo,
  )
  {
  }

  public function storeRangeService(array $request): void
  {
    foreach($request['name'] as $name) {
      $this->rangeRepo->store($name);
    }
  }

  public function storeRangeFeeService(array $request): void
  {
    $this->feeRepo->storeRangeFee($request);
  }

  public function storeRentalService(array $request): void
  {
    $data = [];
    foreach($request['brand'] as $index => $brand) {
      $data['brand'] = $brand;
      $data['model'] = $request['model'][$index];
      $this->rentalRepo->store($data);
    }
  }

  public function storeRentalFeeService(array $request): void
  {
    $this->feeRepo->storeRentalFee($request);
  }

  public function storeShowerFeeService(array $request): void
  {
    $this->feeRepo->storeShowerFee($request);
  }


}