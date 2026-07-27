<?php
namespace App\Models;

use App\Core\Database;

class Favorite {
  public static function folderIdsForStaff(int $staffId): array {
    $stmt = Database::conn()->prepare('SELECT folder_id FROM staff_favorites WHERE staff_id=? AND folder_id IS NOT NULL');
    $stmt->execute([$staffId]);
    $rows = $stmt->fetchAll();
    $ids = [];
    foreach ($rows as $row) {
      $ids[(int)$row['folder_id']] = true;
    }
    return $ids;
  }

  public static function resourceIdsForStaff(int $staffId): array {
    $stmt = Database::conn()->prepare('SELECT resource_id FROM staff_favorites WHERE staff_id=? AND resource_id IS NOT NULL');
    $stmt->execute([$staffId]);
    $rows = $stmt->fetchAll();
    $ids = [];
    foreach ($rows as $row) {
      $ids[(int)$row['resource_id']] = true;
    }
    return $ids;
  }

  public static function toggleFolder(int $staffId, int $folderId): bool {
    $stmt = Database::conn()->prepare('SELECT id FROM staff_favorites WHERE staff_id=? AND folder_id=? LIMIT 1');
    $stmt->execute([$staffId, $folderId]);
    $row = $stmt->fetch();
    if ($row) {
      Database::conn()->prepare('DELETE FROM staff_favorites WHERE id=?')->execute([(int)$row['id']]);
      return false;
    }
    Database::conn()->prepare('INSERT INTO staff_favorites (staff_id, folder_id) VALUES (?,?)')->execute([$staffId, $folderId]);
    return true;
  }

  public static function toggleResource(int $staffId, int $resourceId): bool {
    $stmt = Database::conn()->prepare('SELECT id FROM staff_favorites WHERE staff_id=? AND resource_id=? LIMIT 1');
    $stmt->execute([$staffId, $resourceId]);
    $row = $stmt->fetch();
    if ($row) {
      Database::conn()->prepare('DELETE FROM staff_favorites WHERE id=?')->execute([(int)$row['id']]);
      return false;
    }
    Database::conn()->prepare('INSERT INTO staff_favorites (staff_id, resource_id) VALUES (?,?)')->execute([$staffId, $resourceId]);
    return true;
  }
}
