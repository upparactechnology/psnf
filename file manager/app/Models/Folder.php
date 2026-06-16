<?php
namespace App\Models;

use App\Core\Database;

class Folder {
  public static function create(string $name, ?int $parentId = null): void {
    Database::conn()->prepare('INSERT INTO folders (name,parent_id) VALUES (?,?)')->execute([$name, $parentId]);
  }

  public static function update(int $id, string $name, ?int $parentId = null): void {
    Database::conn()->prepare('UPDATE folders SET name=?, parent_id=? WHERE id=?')->execute([$name, $parentId, $id]);
  }

  public static function delete(int $id): void {
    Database::conn()->prepare('DELETE FROM folders WHERE id=?')->execute([$id]);
  }

  public static function all(): array {
    return Database::conn()->query('SELECT * FROM folders ORDER BY id DESC')->fetchAll();
  }

  public static function childrenOf(int $parentId): array {
    $stmt = Database::conn()->prepare('SELECT * FROM folders WHERE parent_id=? ORDER BY id DESC');
    $stmt->execute([$parentId]);
    return $stmt->fetchAll();
  }

  public static function assignedToStaff(int $staffId): array {
    $sql = 'SELECT DISTINCT f.* FROM folders f
      LEFT JOIN folder_staff fs ON fs.folder_id=f.id
      LEFT JOIN resources r ON r.folder_id=f.id
      LEFT JOIN resource_staff rs ON rs.resource_id=r.id
      WHERE fs.staff_id=? OR rs.staff_id=?';
    $stmt = Database::conn()->prepare($sql);
    $stmt->execute([$staffId, $staffId]);
    $directFolders = $stmt->fetchAll();

    if (empty($directFolders)) {
      return [];
    }

    $allFolders = Database::conn()->query('SELECT * FROM folders')->fetchAll();
    $childrenMap = [];
    $folderById = [];
    foreach ($allFolders as $f) {
      $folderById[(int)$f['id']] = $f;
      $pid = (int)($f['parent_id'] ?? 0);
      if ($pid > 0) {
        $childrenMap[$pid][] = (int)$f['id'];
      }
    }

    $allowedIds = [];
    foreach ($directFolders as $f) {
      $allowedIds[] = (int)$f['id'];
    }

    $getDescendants = function(int $id) use (&$getDescendants, $childrenMap, &$allowedIds): void {
      if (isset($childrenMap[$id])) {
        foreach ($childrenMap[$id] as $childId) {
          if (!in_array($childId, $allowedIds, true)) {
            $allowedIds[] = $childId;
            $getDescendants($childId);
          }
        }
      }
    };

    foreach ($directFolders as $f) {
      $getDescendants((int)$f['id']);
    }

    $result = [];
    foreach ($allowedIds as $id) {
      if (isset($folderById[$id])) {
        $result[] = $folderById[$id];
      }
    }

    usort($result, fn($a, $b) => (int)$b['id'] <=> (int)$a['id']);
    return $result;
  }

  public static function canStaffAccess(int $staffId, int $folderId): bool {
    $allowed = self::assignedToStaff($staffId);
    foreach ($allowed as $f) {
      if ((int)$f['id'] === $folderId) {
        return true;
      }
    }
    return false;
  }
}
