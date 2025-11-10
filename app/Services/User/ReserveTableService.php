<?php
namespace App\Services\User;

class ReserveTableService
{
  public function getMatrix(array $timeSlots, array $rows, string $date, int $userId)
  {
    $reservationByFacility = $this->arrangeByFacility($rows);
    $matrix = [];

    foreach ($reservationByFacility as $name => $tables) {
      $matrix[$name] = $this->buildReserveMatrix($timeSlots, $tables, $date, $userId);
    }

    return $matrix;
  }

  /** 施設別の予約データを時間枠毎に整形 */
  private function buildReserveMatrix(array $timeSlots, array $tables, string $date, int $userId): array
  {
    $matrix = [];

    foreach ($timeSlots as $slot) {
        $time = $slot['value'];
        $status = 'empty';

        foreach ($tables as $table) {
            if ($table['mainte_date'] === $date) {
                $status = 'mainte';
                break;
            }

            if ($time >= $table['start_time'] && $time < $table['end_time']) {
                $status = $table['user_id'] === $userId ? 'own' : 'other';
                break;
            }
        }
        $matrix[] = [
            'time' => $time,
            'status' => $status,
        ];
    }

    return $matrix;
  }

    /** 施設別の予約データに再配列 */
  private function arrangeByFacility(array $reservations): array
  {
    $reservationByFacility =  [];
    foreach ($reservations as $reservation) {
      $reservationByFacility[$reservation['name']][] = [
        'mainte_date' => $reservation['mainte_date'],
        'start_time' =>  $reservation['start_time'],
        'end_time' => $reservation['end_time'],
        'user_id' => $reservation['user_id']
      ];
    }

    return $reservationByFacility;
  }
}