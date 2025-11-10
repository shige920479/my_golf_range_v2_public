<?php
namespace App\Repositories;

use App\Database\DbConnect;

class ReserveShowerRepository
{
  public function __construct(
    private DbConnect $db,
  )
  {}

  public function store(array $data): int
  {
    $sql = "INSERT INTO reserveShower
            (user_id, reserveRange_id, reserve_date, start_time, end_time, fee)
            VALUES
            (:user_id, :reserveRange_id, :reserve_date, :start_time, :end_time, :fee)
            ";
    
    $param = [
      'user_id' => $data['user_id'],
      'reserveRange_id' => $data['reserveRange_id'],
      'reserve_date' => $data['reserve_date'],
      'start_time' => $data['shower_time'],
      'end_time' => $data['shower_end'],
      'fee' => $data['shower_fee']
    ];

    return $this->db->execute($sql, $param);
  }

  /** 
   * 予約重複確認
   * 更新時は 'update', 'user_id' をセット
   */
  public function isReservableTime(string $reserveDate, string $start, int $userId, string $method = 'store') {

    $sql = "SELECT COUNT(*) FROM reserveShower
            WHERE reserve_date = :reserve_date
            AND start_time = :start_time
            AND cancelled = 0";

    $param = [
      'reserve_date' => $reserveDate,
      'start_time' => $start
    ];

    if ($method === 'update') {
      $sql .= ' ' . 'AND NOT user_id = :user_id';
      $param['user_id'] = $userId;
    }

    return (int)$this->db->fetchColumn($sql, $param) === 0;
  }

  public function exists(int $id): bool
  {
    $sql = "SELECT COUNT(*) FROM reserveShower
            WHERE reserveRange_id = :reserveRange_id
            AND cancelled = 0";

    return (int)$this->db->fetchColumn($sql, ['reserveRange_id' => $id]) > 0;
  }

  public function update(int $id, array $data): int
  {
    $sql = "UPDATE reserveShower SET
            reserve_date = :reserve_date, start_time = :start_time, end_time = :end_time, fee = :fee
            WHERE reserveRange_id = :reserveRange_id";
    
    $param = [
      'reserve_date' => $data['reserve_date'],
      'start_time' => $data['shower_time'],
      'end_time' => $data['shower_end'],
      'fee' => $data['shower_fee'],
      'reserveRange_id' => $id
    ];

    return $this->db->execute($sql, $param);
  }

  public function cancel(int $id): int
  {
    $sql = "UPDATE reserveShower SET cancelled = 1 WHERE reserveRange_id = :reserveRange_id";

    return $this->db->execute($sql, ['reserveRange_id' => $id]);
  }
}