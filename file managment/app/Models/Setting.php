<?php
namespace App\Models;

use App\Core\Database;

class Setting {
  public static function get(string $key, $default = null) {
    $stmt = Database::conn()->prepare('SELECT value FROM settings WHERE `key`=? LIMIT 1');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
  }

  public static function set(string $key, string $value): void {
    $sql = 'INSERT INTO settings (`key`, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value)';
    Database::conn()->prepare($sql)->execute([$key, $value]);
  }
}
