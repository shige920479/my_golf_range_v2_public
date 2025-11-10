<?php
namespace App\Repositories;

use App\Database\DbConnect;

class OwnerRepository
{
  public function __construct(private DbConnect $db)
  {}

  /** メールアドレスによる存在確認 */
  public function existsByEmail(string $email): int
  {
    $sql = "SELECT COUNT(*) FROM owner WHERE email = :email";
    $result = $this->db->fetchColumn($sql, ['email' => $email]);

    return $result === false ? 0 : (int)$result;
  }

  /** オーナー登録情報の取得 */
  public function findByEmail(string $email): array|false
  {
    $sql = "SELECT * FROM owner WHERE email = :email";

    return $this->db->fetch($sql, ['email' => $email]);
  }
}