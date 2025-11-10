<?php
require_once __DIR__ . '/../../helpers/ReflectionHelper.php';
use App\Database\DbConnect;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DbConnectTest extends TestCase
{
  use ReflectionHelper;
  private DbConnect $db;

  protected function setUp(): void
  {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NULLABLE,
            age INTEGER NOT NULL
        )
    ");

    $this->db = new DbConnect($pdo);
  }

  #[Test]
  public function db_can_insert_and_fetch_record(): void
  {
    $sql = "INSERT INTO users (name, age) values (:name, :age)";
    $param = ['name' => 'taro', 'age' => 18];
    $this->db->execute($sql, $param);
    $id = $this->db->lastInsertId();

    $param = ['id' => $id];
    $row = $this->db->fetch("SELECT * FROM users WHERE id = :id", $param);

    $this->assertArrayHasKey('name', $row);
    $this->assertSame('taro', $row['name']);
    $this->assertSame(18, $row['age']);
  }
  
  #[Test]
  public function db_can_update_and_fetch_record(): void
  {
    $sql = "INSERT INTO users (name, age) values (:name, :age)";
    $param = ['name' => 'taro', 'age' => 18];
    $this->db->execute($sql, $param);
    $id = $this->db->lastInsertId();
    
    $param = ['id' => $id, 'age' => 25];
    $this->db->execute('UPDATE users SET age = :age WHERE id = :id', $param);
    $param = ['id' => $id];
    $row = $this->db->fetch("SELECT * FROM users WHERE id = :id", $param);

    $this->assertSame(25, $row['age']);
  }

  #[Test]
  public function db_can_delete_and_fetch_record(): void
  {
    $sql = "INSERT INTO users (name, age) values (:name, :age)";
    $param = ['name' => 'taro', 'age' => 18];
    $this->db->execute($sql, $param);
    $id = $this->db->lastInsertId();
    
    $param = ['id' => $id];
    $this->db->execute('DELETE FROM users WHERE id = :id', $param);
    $param = ['id' => $id];
    $row = $this->db->fetchColumn("SELECT count(*) FROM users WHERE id = :id", $param);

    $this->assertSame(0, (int)$row);
  }

  #[Test]
  public function db_can_not_update_and_delete_returns_0(): void
  {
    $param = ['id' => 1, 'age' => 25];
    $result = $this->db->execute('UPDATE users SET age = :age WHERE id = :id', $param);
    $this->assertSame(0, $result);

    $param = ['id => 1'];
    $result = $this->db->execute('DELETE FROM users WHERE id = :id', $param);
    $this->assertSame(0, $result);
  }
  
  #[Test]
  public function db_can_fetchAll_multi_records(): void
  {
    $sql = "INSERT INTO users (name, age) values ('taro', 18), ('hanako', 20)";
    $this->db->execute($sql);

    $row = $this->db->fetchAll('SELECT * FROM users');
    $this->assertCount(2, $row);
    $this->assertSame(['id','name', 'age'], array_keys($row[0]));
  }

  #[Test]
  public function db_can_fetchAllColumn_multi_records(): void
  {
    $sql = "INSERT INTO users (name, age) values ('taro', 18), ('hanako', 20)";
    $this->db->execute($sql);

    $row = $this->db->fetchAllColumn('SELECT age FROM users ORDER BY id ASC');
    $this->assertSame([18, 20], $row);
  }

  #[Test]
  public function db_transaction_can_commit(): void
  {
    $sql1 = "INSERT INTO users (name, age) values ('taro', 18)";
    $sql2 = "INSERT INTO users (name, age) values ('hanako', 20)";

      $this->db->beginTransaction();
      $this->db->execute($sql1);
      $this->db->execute($sql2);
      $this->db->commit();

    $row = $this->db->fetchAll('SELECT * FROM users');
    $this->assertCount(2, $row);
  }

  #[Test]
  public function db_transaction_can_rollback(): void
  {
    $sql1 = "INSERT INTO users (name, age) values ('taro', 18)";
    $sql2 = "INSERT INTO users (name, age) values ('hanako', 20)";

    try {
      $this->db->beginTransaction();
      $this->db->execute($sql1);
      throw new \RuntimeException('force rollback');;
      $this->db->execute($sql2);
      $this->db->commit();
    
    } catch (\Throwable $e) {
      $this->db->rollBack();
    }
  
    $row = $this->db->fetchColumn('SELECT count(*) FROM users');
    $this->assertSame(0, (int)$row);
  }

  #[Test]
  public function db_can_bind_params_with_numeric_keys(): void
  {
    $this->db->execute(
        "INSERT INTO users (name, age) VALUES (?, ?)",
        ['taro', 20]
    );
    $user = $this->db->fetch("SELECT * FROM users WHERE name = :name", ['name' => 'taro']);
    $this->assertSame(20, $user['age']);
  }

  #[Test]
  public function db_can_bind_params_with_limit_offset(): void
  {
    for($i = 1; $i < 11; $i++) {
      $this->db->execute(
        "INSERT INTO users (name, age) VALUES (?, ?)",
        ['taro_' . $i , 20]
      );
    }
    $rows = $this->db->fetchAll(
      'SELECT * FROM users LIMIT :limit OFFSET :offset',
      ['limit' => 3, 'offset' => 2]
    );

    $this->assertCount(3, $rows);
    $this->assertSame('taro_3', $rows[0]['name']);
  }

  #[Test]
  public function db_can_bind_params_with_bool_and_null(): void
  {
      $this->db->execute(
          "INSERT INTO users (name, age) VALUES (:name, :age)",
          ['name' => null, 'age' => 30]
      );
      $rows = $this->db->fetch("SELECT * FROM users WHERE age = :age", ['age' => 30]);
      $this->assertNull($rows['name']);

      $this->db->execute(
          "INSERT INTO users (name, age) VALUES (:name, :age)",
          ['name' => 'booltest', 'age' => true]
      );
      $rows = $this->db->fetch("SELECT * FROM users WHERE name = :name", ['name' => 'booltest']);
      $this->assertSame(1, $rows['age']); // SQLite では true → 1
  }

  // コード修正のため追加
  #[Test]
  public function db_user_provider_pdo():void
  {
    $pdoMock = $this->createMock(PDO::class);
    $db = new DbConnect($pdoMock);
    $prop = $this->getProperty($db, 'pdo');

    $this->assertSame($pdoMock, $prop);
  }
}