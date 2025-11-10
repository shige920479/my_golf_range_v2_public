<?php
namespace App\Repositories;

use App\Database\DbConnect;

class RentalRepository
{
  public function __construct(
    private DbConnect $db,
  )
  {
  }

  /** レンタルクラブ登録 */
  public function store(array $data): int
  {
    $sql = "INSERT INTO rental (brand, model) VALUES (:brand, :model)";
    
    return $this->db->execute($sql, [
      'brand' => $data['brand'],
      'model' => $data['model']
    ]);
  }

  /** レンタルクラブ情報取得 */
  public function get(): array
  {
    $sql = "SELECT id, brand, model, mainte_date FROM rental WHERE del_flag = 0 ORDER BY brand";

    return $this->db->fetchAll($sql);
  }

  /** 存在チェック */
  public function exists(int $id): bool
  {
    $sql = "SELECT count(*) FROM rental WHERE id = :id";
    $result = $this->db->fetchColumn($sql, ['id' => $id]);

    return (int)$result > 0 ? true : false;
  }

  /** 利用可能日か判定（メンテナンス日ではない） */
  public function isAvailableDate(string $id, string $date): bool
  {
    $sql = "SELECT COUNT(*) FROM rental
            WHERE id = :id AND mainte_date = :mainte_date";
    $param = [
      'id' => $id,
      'mainte_date' => $date
    ];
    
    return ! $this->db->fetchColumn($sql, $param) ? true : false;
  }

  /** brand, model, mainte_date を取得 */
  public function getById(int $id): array|false
  {
    $sql = "SELECT brand, model, mainte_date FROM rental WHERE id = :id";

    return $this->db->fetch($sql, ['id' => $id]);
  }

  /** メンテナンス日の更新 */
  public function updateMainte(string $date, int $id): int
  {
    $sql = "UPDATE rental SET mainte_date = :mainte_date WHERE id = :id";
    $param = [
      'mainte_date' => $date,
      'id' => $id
    ];

    return $this->db->execute($sql, $param);
  }

}