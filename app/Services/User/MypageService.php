<?php
namespace App\Services\User;

use App\Repositories\ReserveRangeRepository;
use Carbon\Carbon;

class MypageService
{
  public function __construct(
    private ReserveRangeRepository $reserveRangeRepo,
  )
  {
  }

  public function getMyReservation(int $userId): array
  {
    $now = Carbon::now();
    $today = $now->format('Y-m-d');
    $currentTime = $now->format('H:i:s');

    $reservations = $this->reserveRangeRepo->getByUserId($userId, $today, $currentTime);

    $reservations = array_map(function ($reserve) {
      $reserve['reserve_date'] = Carbon::parse($reserve['reserve_date'])->isoFormat('M月D日(ddd)');
      $reserve['start_time'] = Carbon::createFromTimeString($reserve['start_time'])->format('H:i');
      $reserve['end_time'] = Carbon::createFromTimeString($reserve['end_time'])->format('H:i');
      if(! empty($reserve['shower_time'])) {
        $reserve['shower_time'] = Carbon::createFromTimeString($reserve['shower_time'])->format('H:i');
      } 
      return $reserve;
    }, $reservations ?: []);

    return [
      'reservations' => $reservations,
      'today' => Carbon::parse($today)->isoFormat('M月D日(ddd)'),
      'expired' => Carbon::createFromTimeString($currentTime)->addMinutes(60)->format('H:i')
    ];
  }
}