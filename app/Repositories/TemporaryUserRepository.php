<?php
namespace App\Repositories;

use App\Database\DbConnect;
use Carbon\Carbon;

class TemporaryUserRepository
{
  public function __construct(private DbConnect $db)
  {}

  /**
   * 仮ユーザー登録
   * @return int 成功時は件数、失敗時は0
   */
  public function store(string $email, string $hashed): int
  {
    $expired = $this->getExpired();
    $sql = "INSERT INTO temp_users (email, url_token, expired_at)
            VALUES (:email, :url_token, :expired_at)";
    $param = [
      'email' => $email,
      'url_token' => $hashed,
      'expired_at' => $expired
    ];

    return $this->db->execute($sql, $param);
  }

  /**
   * 有効期限判定
   * @return int 有効:1 無効:0
   */
  public function isBeforeExpired(string $email): int
  {
    $now = $this->getNow();
    $sql = "SELECT count(*) FROM temp_users WHERE email = :email AND expired_at > :now";

    $result = $this->db->fetchColumn($sql, [
      'email' => $email,
      'now' => $now
    ]);

    return (int)$result;
  }

  public function getByEmail(string $email): array|false
  {
    $now = $this->getNow();
    $sql = "SELECT email, url_token, expired_at FROM temp_users 
            WHERE email = :email AND expired_at > :now";

    return $this->db->fetch($sql, [
      'email' => $email,
      'now' => $now
    ]);
  }
  /**
   * url_tokenの検証
   * @return array|false 成功時は array['email']
   */
  public function verify(string $hashed): array|false
  {
    $now = $this->getNow();
    $sql = "SELECT email FROM temp_users 
            WHERE url_token = :hashed AND expired_at > :now";

    return $this->db->fetch($sql, [
      'hashed' => $hashed,
      'now' => $now
    ]);
  }
  /**
   * 仮ユーザー登録の削除
   * @return int 成功時は件数、失敗時は0
   */
  public function delete(string $hashed, string $email): int
  {
    $sql = "DELETE FROM temp_users
            WHERE url_token = :hashed AND email = :email";

    return $this->db->execute($sql, [
      'hashed' => $hashed,
      'email' => $email,
    ]);
  }

  private function getExpired(): string
  {
    return Carbon::now()->addMinutes(60 * 24)->format("Y-m-d H:i:s");
  }

  private function getNow(): string
  {
    return Carbon::now()->format("Y-m-d H:i:s");
  }

}