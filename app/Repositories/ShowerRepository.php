<?php
namespace App\Repositories;

use App\Database\DbConnect;

class ShowerRepository
{
  public function __construct(
    private DbConnect $db,
  )
  {
  }

  public function get(): array
  {
    $sql = "SELECT id, mainte_date FROM shower";

    return $this->db->fetchAll($sql);
  }
  public function getById(): array
  {
    $sql = "SELECT id, mainte_date FROM shower";

    return $this->db->fetch($sql);
  }

  /** 利用可能日か判定（メンテナンス日ではない） */
  public function isAvailableDate(string $date): bool
  {
    $sql = "SELECT COUNT(*) FROM shower
            WHERE mainte_date = :mainte_date";
    $param = ['mainte_date' => $date];
    
    return ! $this->db->fetchColumn($sql, $param) ? true : false;
  }

  /** メンテナンス日の更新 */
  public function updateMainte(string $date, int $id): int
  {
    $sql = "UPDATE shower SET mainte_date = :mainte_date WHERE id = :id";
    $param = [
      'mainte_date' => $date,
      'id' => $id
    ];

    return $this->db->execute($sql, $param);
  }

}