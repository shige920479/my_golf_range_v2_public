<?php
namespace App\Repositories;

use App\Database\DbConnect;

class RangeRepository
{
  public function __construct(
    private DbConnect $db,
  )
  {
  }

  /** レンジ登録 */
  public function store(string $data): int
  {
    $sql = "INSERT INTO drivingRange (name) VALUES (:name)";
    return $this->db->execute($sql, ['name' => $data]);
  }

  /** 全レンジ情報の取得 */
  public function get(): array
  {
    $sql = "SELECT id, name, mainte_date FROM drivingRange WHERE del_flag = 0 ORDER BY id ASC";

    return $this->db->fetchAll($sql);
  }

  /** 存在チェック */
  public function exists(int $id): bool
  {
    $sql = "SELECT count(*) FROM drivingRange WHERE id = :id AND del_flag = 0";
    $result = $this->db->fetchColumn($sql, ['id' => $id]);

    return (int)$result > 0;
  }

  /** 利用可能日か判定（メンテナンス日ではない） */
  public function isAvailableDate(int $id, string $date): bool
  {
    $sql = "SELECT COUNT(*) FROM drivingRange
            WHERE id = :id AND mainte_date = :mainte_date";
    $param = [
      'id' => $id,
      'mainte_date' => $date
    ];

    $result = (int)$this->db->fetchColumn($sql, $param);
    
    return $result === 0;
  }

  /** range名 を取得 */
  public function getById(int $id): array|false
  {
    $sql = "SELECT id, name, mainte_date FROM drivingRange WHERE id = :id AND del_flag = 0";

    return $this->db->fetch($sql, ['id' => $id]);
  }

  /** メンテナンス日取得 */
  public function getMainte(string $date): array
  {
    $sql = "SELECT 
            'range' as facility, dr.id AS id, dr.name AS name, NULL AS model,
            IF(dr.mainte_date < :date, NULL, dr.mainte_date) AS mainte_date
            FROM drivingRange as dr
            UNION
            SELECT
            'rental' as facility, re.id AS id, re.brand AS name, re.model AS model,
            IF(re.mainte_date < :date, NULL, re.mainte_date) AS mainte_date
            FROM rental as re
            UNION
            SELECT
            'shower' as facility, sh.id AS id, NULL AS name, NULL AS model,
            IF(sh.mainte_date < :date, NULL, sh.mainte_date) AS mainte_date
            FROM shower as sh";

    return $this->db->fetchAll($sql, ['date' => $date]);
  }

  /** メンテナンス日の更新 */
  public function updateMainte(string $date, int $id): int
  {
    $sql = "UPDATE drivingRange SET mainte_date = :mainte_date WHERE id = :id";
    $param = [
      'mainte_date' => $date,
      'id' => $id
    ];

    return $this->db->execute($sql, $param);
  }





}