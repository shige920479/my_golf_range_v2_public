<?php
namespace App\Repositories;

use App\Database\DbConnect;

class ReserveRentalRepository
{
  public function __construct(
    private DbConnect $db,
  )
  {}

  public function unionRentalAndShowerReservation(string $reserveDate): array
  {
    $sql = "SELECT
            CONCAT(re.brand, '/' ,re.model) AS name, re.mainte_date AS mainte_date,
            rre.start_time AS start_time, rre.end_time AS end_time, rre.user_id AS user_id
            FROM rental AS re
            LEFT JOIN
            reserveRental AS rre
            ON re.id = rre.rental_id
            AND rre.reserve_date = :reserve_date
            AND rre.cancelled = 0
            UNION ALL
            SELECT
            'シャワールーム' AS name, sh.mainte_date AS mainte_date, rsw.start_time AS start_time,
            rsw.end_time AS end_time, rsw.user_id AS user_id
            FROM shower AS sh
            LEFT JOIN
            reserveShower AS rsw
            ON rsw.reserve_date = :reserve_date
            AND rsw.cancelled = 0
            ORDER BY name, start_time";

      return $this->db->fetchAll($sql, ['reserve_date' => $reserveDate]);
  }

  public function store(array $data): int
  {
    $sql = "INSERT INTO reserveRental
            (user_id, rental_id, reserveRange_id, reserve_date, start_time, end_time, fee)
            VALUES
            (:user_id, :rental_id, :reserveRange_id, :reserve_date, :start_time, :end_time, :fee)
            ";
    
    $param = [
      'user_id' => $data['user_id'],
      'rental_id' => $data['rental_id'],
      'reserveRange_id' => $data['reserveRange_id'],
      'reserve_date' => $data['reserve_date'],
      'start_time' => $data['start_time'],
      'end_time' => $data['end_time'],
      'fee' => $data['rental_fee']
    ];

    return $this->db->execute($sql, $param);
  }

  /** 
   * 予約重複確認
   * 更新時は 'update', 'user_id' をセット
   */
  public function isReservableTime(int $rentalId, string $reserveDate, string $start, string $end, int $userId, string $method = 'store') {

    $sql = "SELECT COUNT(*) FROM reserveRental
            WHERE reserve_date = :reserve_date
            AND rental_id = :rental_id
            AND start_time < :end_time
            AND end_time > :start_time
            AND cancelled = 0";

    $param = [
      'reserve_date' => $reserveDate,
      'rental_id' => $rentalId,
      'start_time' => $start,
      'end_time' => $end
    ];

    if ($method === 'update') {
      $sql .= ' ' . 'AND NOT user_id = :user_id';
      $param['user_id'] = $userId;
    }

    return (int)$this->db->fetchColumn($sql, $param) === 0;
  }

  public function exists(int $id): bool
  {
    $sql = "SELECT COUNT(*) FROM reserveRental
            WHERE reserveRange_id = :reserveRange_id
            AND cancelled = 0";

    return (int)$this->db->fetchColumn($sql, ['reserveRange_id' => $id]) > 0;
  }

  public function update(int $id, array $data): int
  {
    $sql = "UPDATE reserveRental
            SET
            rental_id = :rental_id, reserve_date = :reserve_date,
            start_time = :start_time, end_time = :end_time, fee = :fee
            WHERE reserveRange_id = :reserveRange_id";
    
    $param = [
      'rental_id' => (int)$data['rental_id'],
      'reserve_date' => $data['reserve_date'],
      'start_time' => $data['start_time'],
      'end_time' => $data['end_time'],
      'fee' => $data['rental_fee'],
      'reserveRange_id' => $id
    ];

    return $this->db->execute($sql, $param);
  }
  public function cancel(int $id): int
  {
    $sql = "UPDATE reserveRental SET cancelled = 1 WHERE reserveRange_id = :reserveRange_id";

    return $this->db->execute($sql, ['reserveRange_id' => $id]);
  }

}