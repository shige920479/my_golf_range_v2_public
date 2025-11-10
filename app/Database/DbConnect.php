<?php
namespace App\Database;

use PDO;

class DbConnect
{
  private static ?PDO $pdo = null;

  public function __construct(?PDO $pdo = null)
  {
    if ($pdo) {
      self::$pdo = $pdo;
      return;
    } 
    
    if (self::$pdo === null) {
      if (DB_CONNECTION === 'sqlite') {
        $dsn = 'sqlite:' . DB_NAME;
        self::$pdo = new PDO($dsn);
      } else {
        $dsn = sprintf(
          "mysql:host=%s;dbname=%s;charset=utf8mb4",
          DB_HOST,
          DB_NAME
        );
      }
      self::$pdo = new PDO($dsn, DB_USER, DB_PASS);
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
  }

  public function beginTransaction(): void
  {
    if (self::$pdo->inTransaction()) return;
    self::$pdo->beginTransaction();
  }

  public function commit(): void
  {
    if (! self::$pdo->inTransaction()) return;
    self::$pdo->commit();
  }

  public function rollBack(): void
  {
    if (! self::$pdo->inTransaction()) return;
    self::$pdo->rollBack();
  }

  public function fetch(string $sql, array $param = []): array|false
  {
    $stmt = self::$pdo->prepare($sql);
    $this->bindParams($stmt, $param);
    $stmt->execute();

    return $stmt->fetch();
  }

  public function fetchAll(string $sql, array $param = []): array
  {
    $stmt = self::$pdo->prepare($sql);
    $this->bindParams($stmt, $param);
    $stmt->execute();

    return $stmt->fetchAll();
  }

  public function execute(string $sql, array $param = []): int
  {
    $stmt = self::$pdo->prepare($sql);
    $this->bindParams($stmt, $param);
    $stmt->execute();
    return (int)$stmt->rowCount();
  }

  /** 
   * count(*)と併用
   */
  public function fetchColumn(string $sql, array $param = []): string|false
  {
    $stmt = self::$pdo->prepare($sql);
    $this->bindParams($stmt, $param);
    $stmt->execute();

    return $stmt->fetchColumn();
  }

  /**
   * fetchAllのvalueだけ1次元配列でほしい時に使用
   */
  public function fetchAllColumn(string $sql, array $param = []): array
  {
    $stmt = self::$pdo->prepare($sql);
    $this->bindParams($stmt, $param);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function lastInsertId(): int
  {
    return self::$pdo->lastInsertId();
  }

  private function bindParams(\PDOStatement $stmt, array $param): void
  {
    $position = 1;

    foreach($param as $key => $value) {
      if (is_int($key)) {
        $type = is_int($value) ? PDO::PARAM_INT :
                (is_bool($value) ? PDO::PARAM_BOOL :
                (is_null($value) ? PDO::PARAM_NULL : PDO::PARAM_STR));
        $stmt->bindValue($position++, $value, $type);
      } else {
        $paramKey = ':' . ltrim($key, ':');
        if (in_array($key, ['limit', 'offset'])) {
          $stmt->bindValue($paramKey, (int)$value, PDO::PARAM_INT);
        } elseif (is_int($value)) {
          $stmt->bindValue($paramKey, $value, \PDO::PARAM_INT);
        } elseif (is_bool($value)) {
          $stmt->bindValue($paramKey, $value, \PDO::PARAM_BOOL);
        } elseif (is_null($value)) {
          $stmt->bindValue($paramKey, $value, \PDO::PARAM_NULL);
        } else {
          $stmt->bindValue($paramKey, $value, \PDO::PARAM_STR);
        }
      }
    }

  }
}