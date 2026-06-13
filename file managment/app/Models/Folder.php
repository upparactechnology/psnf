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
    $stmt = Database::conn()->prepare($sql); $stmt->execute([$staffId, $staffId]);
    return $stmt->fetchAll();
  }

  public static function canStaffAccess(int $staffId, int $folderId): bool {
    $sql = 'SELECT COUNT(*) c FROM folders f
      LEFT JOIN folder_staff fs ON fs.folder_id=f.id
      LEFT JOIN resources r ON r.folder_id=f.id
      LEFT JOIN resource_staff rs ON rs.resource_id=r.id
      WHERE f.id=? AND (fs.staff_id=? OR rs.staff_id=?)';
    $stmt = Database::conn()->prepare($sql);
    $stmt->execute([$folderId, $staffId, $staffId]);
    return (int)$stmt->fetch()['c'] > 0;
  }
}
