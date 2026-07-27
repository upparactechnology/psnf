<?php
namespace App\Core;

use PDO;

class Database {
  public static function conn(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $db = require __DIR__ . '/../../config/database.php';
    $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['username'], $db['password'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Self-healing migration for sort_order
    try {
      $pdo->query("SELECT sort_order FROM resources LIMIT 1");
    } catch (\Throwable $e) {
      try {
        $pdo->exec("ALTER TABLE resources ADD COLUMN sort_order INT NOT NULL DEFAULT 0");
      } catch (\Throwable $ex) {}
    }

    return $pdo;
  }
}
