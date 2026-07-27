<?php
namespace App\Models;

use App\Core\Database;

class Audit {
  public static function log(?int $adminId, ?int $staffId, string $event, string $meta=''): void {
    $ip=$_SERVER['REMOTE_ADDR'] ?? 'NA';
    $ua=$_SERVER['HTTP_USER_AGENT'] ?? 'NA';
    $sql='INSERT INTO activity_logs (admin_id,staff_id,event,meta,ip,user_agent) VALUES (?,?,?,?,?,?)';
    Database::conn()->prepare($sql)->execute([$adminId,$staffId,$event,$meta,$ip,$ua]);
  }

  public static function recent(int $limit=30): array {
    $stmt=Database::conn()->prepare('SELECT * FROM activity_logs ORDER BY id DESC LIMIT ?');
    $stmt->bindValue(1,$limit,\PDO::PARAM_INT); $stmt->execute();
    return $stmt->fetchAll();
  }

  public static function recentByStaff(int $staffId, int $limit=30): array {
    $stmt=Database::conn()->prepare('SELECT * FROM activity_logs WHERE staff_id=? ORDER BY id DESC LIMIT ?');
    $stmt->bindValue(1,$staffId,\PDO::PARAM_INT);
    $stmt->bindValue(2,$limit,\PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }
}
