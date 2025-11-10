<?php
namespace App\Repositories;

use App\Database\DbConnect;
use Carbon\Carbon;
use InvalidArgumentException;

class FeeRepository
{
  private array $allowedTable = ['rangeFee', 'rentalFee', 'showerFee'];

  public function __construct(
    private DbConnect $db)
  {}

  public function getCurrentFee(string $table): array|bool
  {
    if (! in_array($table, $this->allowedTable)) {
      throw new InvalidArgumentException("許可されていないテーブル: {$table}");
    }

    $sql = "SELECT * FROM {$table}
            WHERE effective_date <= :today
            ORDER BY effective_date DESC
            LIMIT 1";
    
    return $this->db->fetch($sql, ['today' => date('Y-m-d')]);
  }

  public function getChangeFee(string $table): array|bool
  {
    if (! in_array($table, $this->allowedTable)) {
      throw new InvalidArgumentException("許可されていないテーブル: {$table}");
    }

    $sql = "SELECT * FROM {$table}
            WHERE effective_date > :today
            ORDER BY effective_date ASC
            LIMIT 1";

    return $this->db->fetch($sql, ['today' => date('Y-m-d')]);
  }

  public function getFee(string $table, string $date): array
  {
    if (! in_array($table, $this->allowedTable)) {
      throw new InvalidArgumentException("許可されていないテーブル: {$table}");
    }

    $sql = "SELECT * FROM {$table} 
            WHERE effective_date <= :reserve_date
            ORDER BY effective_date DESC
            LIMIT 1";
    
    return $this->db->fetch($sql, ['reserve_date' => $date]);
  }

  public function storeRangeFee(array $data): int
  {
    $sql = "INSERT INTO rangeFee
            (entrance_fee, weekday_fee, holiday_fee, effective_date)
            VALUES
            (:entrance_fee, :weekday_fee, :holiday_fee, :effective_date)";
    $param = [
      'entrance_fee' => $data['entrance_fee'],
      'weekday_fee' => $data['weekday_fee'],
      'holiday_fee' => $data['holiday_fee'],
      'effective_date' => $data['effective_date']
    ];

    return $this->db->execute($sql, $param);
  }

  public function storeRentalFee(array $data): int
  {
    $sql = "INSERT INTO rentalFee (rental_fee, effective_date)
            VALUES (:rental_fee, :effective_date)";
    $param = [
      'rental_fee' => $data['rental_fee'],
      'effective_date' => $data['effective_date']
    ];

    return $this->db->execute($sql, $param);
  }

  public function storeShowerFee(array $data): int
  {
    $sql = "INSERT INTO showerFee (shower_fee, effective_date)
            VALUES (:shower_fee, :effective_date)";
    $param = [
      'shower_fee' => $data['shower_fee'],
      'effective_date' => $data['effective_date']
    ];

    return $this->db->execute($sql, $param);
  }
}