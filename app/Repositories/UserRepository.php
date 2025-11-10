<?php
namespace App\Repositories;

use App\Database\DbConnect;
use PharIo\Manifest\Email;

class UserRepository
{
  public function __construct(private DbConnect $db)
  {}

  /**
   * メールアドレスによる存在確認
   * @param string $email
   * @return int 件数
   */
  public function existsByEmail(string $email): int
  {
    $sql = "SELECT COUNT(*) FROM users WHERE email = :email AND status = 1";
    $result = $this->db->fetchColumn($sql, ['email' => $email]);

    return $result === false ? 0 : (int)$result;
  }

  /**
   * ユーザー登録
   * @return int 成功時は件数、失敗時は0
   */
  public function store(array $inputs): int
  {
    $sql = "INSERT INTO users 
            (firstname, lastname, firstnamekana, lastnamekana, email, phone, gender, password)
            VALUES
            (:firstname, :lastname, :firstnamekana, :lastnamekana, :email, :phone, :gender, :password)";
    $param = [
      'firstname' => $inputs['firstname'],
      'lastname' => $inputs['lastname'],
      'firstnamekana' => $inputs['firstnamekana'],
      'lastnamekana' => $inputs['lastnamekana'],
      'email' => $inputs['email'],
      'phone' => $inputs['phone'],
      'gender' => $inputs['gender'],
      'password' => $inputs['password']
    ];

    return $this->db->execute($sql, $param);
  }

  public function findByEmail(string $email): array|false
  {
    $sql = "SELECT * FROM users WHERE email = :email";

    return $this->db->fetch($sql, ['email' => $email]);
  }
}