<?php
use App\Database\DbConnect;
use PHPUnit\Framework\TestCase;

abstract class BaseRepositoryTestCase extends TestCase
{
  protected ?DbConnect $db = null;

  protected function createPdo(): PDO
  {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // $pdo->exec('PRAGMA foreign_keys = ON;'); // 外部キー制約の有効化（今は不要）

    return $pdo;
  }

  protected function createReserveRange(DbConnect $db): void
  {
    $sql = "CREATE TABLE reserveRange (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            drivingRange_id INTEGER NOT NULL,
            reserve_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            number INTEGER NOT NULL,
            cancelled TINYINT(1) DEFAULT 0,
            fee DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (drivingRange_id) REFERENCES drivingRange(id)
            )";

    $db->execute($sql);
  }
  protected function createReserveRental(DbConnect $db): void
  {
    $sql = "CREATE TABLE reserveRental (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            rental_id INTEGER NOT NULL,
            reserveRange_id INTEGER NOT NULL,
            reserve_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            cancelled TINYINT(1) DEFAULT 0,
            fee DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (rental_id) REFERENCES rental(id),
            FOREIGN KEY (reserveRange_id) REFERENCES reserveRange(id) ON DELETE CASCADE
            )";

    $db->execute($sql);
  }
  protected function createReserveShower(DbConnect $db): void
  {
    $sql = "CREATE TABLE reserveShower (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            reserveRange_id INTEGER NOT NULL,
            reserve_date DATE NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            cancelled TINYINT(1) DEFAULT 0,
            fee DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (reserveRange_id) REFERENCES reserveRange(id) ON DELETE CASCADE
            )";

    $db->execute($sql);
  }
  protected function createDrivingRange(DbConnect $db): void
  {
    $sql = "CREATE TABLE drivingRange (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(50) NOT NULL,
            mainte_date DATE,
            del_flag TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    $db->execute($sql);
  }
  protected function createRental(DbConnect $db): void
  {
    $sql = "CREATE TABLE rental (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            brand VARCHAR(50) NOT NULL,
            model VARCHAR(50) NOT NULL,
            mainte_date DATE,
            del_flag TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    $db->execute($sql);
  }
  protected function createShower(DbConnect $db): void
  {
    $sql = "CREATE TABLE shower (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            mainte_date DATE,
            del_flag TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    $db->execute($sql);
  }
  protected function createUser(DbConnect $db): void
  {
    $sql = "CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            firstname VARCHAR(30) NOT NULL,
            lastname  VARCHAR(30) NOT NULL,
            firstnamekana  VARCHAR(100) NOT NULL,
            lastnamekana VARCHAR(100) NOT NULL,
            email VARCHAR(50) NOT NULL UNIQUE,
            phone VARCHAR(255) NOT NULL,
            gender VARCHAR(10),
            password VARCHAR(255) NOT NULL,
            status TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    $db->execute($sql);
  }
  protected function createRangeFee(DbConnect $db): void
  {
    $sql = "CREATE TABLE rangeFee (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            entrance_fee DECIMAL(10, 2) NOT NULL,
            weekday_fee DECIMAL(10, 2) NOT NULL, 
            holiday_fee DECIMAL(10, 2) NOT NULL,
            effective_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    $db->execute($sql);
  }
  protected function createRentalFee(DbConnect $db): void
  {
    $sql = "CREATE TABLE rentalFee (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            rental_fee DECIMAL(10, 2) NOT NULL, 
            effective_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    $db->execute($sql);
  }
  protected function createShowerFee(DbConnect $db): void
  {
    $sql = "CREATE TABLE showerFee (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            shower_fee DECIMAL(10, 2) NOT NULL, 
            effective_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";

    $db->execute($sql);
  }




}