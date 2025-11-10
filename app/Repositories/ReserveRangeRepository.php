<?php
namespace App\Repositories;

use App\Database\DbConnect;

class ReserveRangeRepository
{
  public function __construct(
    private DbConnect $db,
  )
  {}

  public function getRangeReservation(string $reserveDate): array
  {
    $sql = "SELECT
            dvr.name AS name, dvr.mainte_date AS mainte_date,
            rra.start_time AS start_time, rra.end_time AS end_time, rra.user_id as user_id
            FROM drivingRange AS dvr
            LEFT JOIN
            reserveRange AS rra
            ON rra.drivingRange_id = dvr.id
            AND rra.reserve_date = :reserve_date
            AND rra.cancelled = 0
            ORDER BY dvr.id, rra.start_time;";
    
    return $this->db->fetchAll($sql, ['reserve_date' => $reserveDate]);
  }

  /** 新規予約登録　@return int 挿入された行数（成功時1） */
  public function store(array $data): int
  {
    $sql = "INSERT INTO reserveRange
            (user_id, drivingRange_id, reserve_date, start_time, end_time, number, fee)
            VALUES
            (:user_id, :drivingRange_id, :reserve_date, :start_time, :end_time, :number, :fee)
            ";
    
    $param = [
      'user_id' => $data['user_id'],
      'drivingRange_id' => (int)$data['range_id'],
      'reserve_date' => $data['reserve_date'],
      'start_time' => $data['start_time'],
      'end_time' => $data['end_time'],
      'number' => (int)$data['number'],
      'fee' => $data['range_fee']
    ];

    return $this->db->execute($sql, $param);
  }

  /** 
   * 予約重複確認
   * 更新時は 'update', 'user_id' をセット
   */
  public function isReservableTime(int $rangeId, string $reserveDate, string $start, string $end,
                                   int $userId, string $method = 'store'): bool
  {

    $sql = "SELECT COUNT(*) FROM reserveRange
            WHERE reserve_date = :reserve_date
            AND drivingRange_id = :drivingRange_id
            AND start_time < :end_time
            AND end_time > :start_time
            AND cancelled = 0";

    $param = [
      'reserve_date' => $reserveDate,
      'drivingRange_id' => $rangeId,
      'start_time' => $start,
      'end_time' => $end
    ];

    if ($method === 'update') {
      $sql .= ' ' . 'AND NOT user_id = :user_id';
      $param['user_id'] = $userId;
    }

    return (int)$this->db->fetchColumn($sql, $param) === 0;
  }

  public function isDuplicateSameTime(int $rangeId, string $reserveDate, string $start, string $end, int $userId): bool
  {
    $sql = "SELECT COUNT(*) FROM reserveRange
            WHERE user_id = :user_id
            AND reserve_date = :reserve_date
            AND drivingRange_id != :drivingRange_id
            AND start_time < :start_time
            AND end_time > :end_time
            AND cancelled = 0";
    $param = [
      'user_id' => $userId,
      'reserve_date' => $reserveDate,
      'drivingRange_id' => $rangeId,
      'start_time' => $end,
      'end_time' => $start
    ];

    return (int)$this->db->fetchColumn($sql, $param) > 0;
  }

  public function getByUserId(int $userId, string $date, string $currentTime): array
  {
    $sql = "SELECT
            rra.id AS id, rra.reserve_date AS reserve_date, rra.number AS number,
            rra.start_time AS start_time, rra.end_time AS end_time,
            rsw.start_time AS shower_time, dr.name AS range_name, re.brand AS brand, re.model AS model
            FROM reserveRange AS rra
            LEFT JOIN reserveRental AS rre
            ON rra.id = rre.reserveRange_id AND rre.cancelled = 0
            LEFT JOIN reserveShower AS rsw
            ON rra.id = rsw.reserveRange_id AND rsw.cancelled = 0
            LEFT JOIN drivingRange AS dr
            ON dr.id = rra.drivingRange_id
            LEFT JOIN rental AS re
            ON re.id = rre.rental_id
            WHERE rra.user_id = :user_id
            AND rra.cancelled = 0
            AND (rra.reserve_date > :date OR (rra.reserve_date = :date AND rra.end_time > :current_time))
            ORDER BY rra.reserve_date ASC, rra.start_time ASC";
    
    $param = [
      'user_id' => $userId,
      'date' => $date,
      'current_time' => $currentTime
    ];

    return $this->db->fetchAll($sql, $param);
  }

  public function getById(int $id): array|false
  {
    $sql = "SELECT
            rra.id AS id, rra.drivingRange_id AS range_id, rra.number AS number,
            rra.reserve_date AS reserve_date, rra.start_time AS start_time, rra.end_time AS end_time,
            rre.rental_id AS rental_id, rsw.start_time AS shower_time
            FROM reserveRange AS rra
            LEFT JOIN reserveRental AS rre
            ON rre.reserveRange_id = rra.id AND rre.cancelled = 0
            LEFT JOIN reserveShower AS rsw
            ON rsw.reserveRange_id = rra.id AND rsw.cancelled = 0
            WHERE rra.id = :id
            AND rra.cancelled = 0";

    return $this->db->fetch($sql, ['id' => $id]);
  }

  public function update(int $id, array $data): int
  {
    $sql = "UPDATE reserveRange 
            SET
            drivingRange_id = :drivingRange_id, reserve_date = :reserve_date,
            start_time = :start_time, end_time = :end_time, number = :number, fee = :fee
            WHERE id = :id";
    
    $param = [
      'drivingRange_id' => (int)$data['range_id'],
      'reserve_date' => $data['reserve_date'],
      'start_time' => $data['start_time'],
      'end_time' => $data['end_time'],
      'number' => (int)$data['number'],
      'fee' => $data['range_fee'],
      'id' => $id
    ];

    return $this->db->execute($sql, $param);
  }

  public function cancel(int $id): int
  {
    $sql = "UPDATE reserveRange SET cancelled = 1 WHERE id = :id";

    return $this->db->execute($sql, ['id' => $id]);
  }

  public function isOwnedByUser(int $id, int $userId): bool
  {
    $sql = "SELECT COUNT(*) FROM reserveRange
            WHERE id = :id 
            AND user_id = :user_id
            AND cancelled = 0";
    $param = [
      'id' => $id,
      'user_id' => $userId
    ];

    return (int)$this->db->fetchColumn($sql, $param) > 0;
    
  }


}