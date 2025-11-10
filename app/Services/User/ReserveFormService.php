<?php
namespace App\Services\User;

use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ShowerRepository;
use Carbon\Carbon;

class ReserveFormService
{
  public function __construct(
    private RangeRepository $rangeRepo,
    private RentalRepository $rentalRepo,
    private ShowerRepository $showerRepo
  )
  {
  }

  public function getFormData(): array
  {
    $form = [];
    $form['range'] = $this->rangeRepo->get();
    $form['rental'] = $this->rentalRepo->get();
    $form['shower'] = $this->showerRepo->get();
    
    return $form;
  }

  /** 日付生成　本日～MAX_RESERVE_DATE */
  public function availableDate(): array
  {
    $today = Carbon::today();
    $dates = [];
    for ($i = 0; $i < MAX_RESERVE_DATE; $i++) {
      $date = $today->copy()->addDays($i);
      $dates[] = [
        'value' => $date->format('Y-m-d'),
        'label' => $date->isoformat('MM月DD日(ddd)')
      ];
    }
    return $dates;
  }

  /** 時間枠生成 -初期値 30分間隔/ $adjustで開始時間を調整 */
  public function generateTime(int $adjust = 0, int $interval = 30): array
  {
    $startTime = Carbon::createFromTimeString(OPEN_TIME)->addMinutes($adjust);
    $endTime = Carbon::createFromTimeString(CLOSE_TIME)->addMinutes($adjust);

    $timeTable = [];
    for ($i = $startTime; $i < $endTime; $i->addMinutes($interval)) {
      $timeTable[] = [
        'value' => $i->format('H:i:s'),
        'label' => $i->format('H:i'),
      ];
    }
    return $timeTable;
  }

  public function getRentalData(): array
  {
    return $this->rentalRepo->get();
  }
}