<?php
namespace App\Models;

use App\Core\Database;

class Resource {
  public static function create(array $data): int {
    $sql='INSERT INTO resources (folder_id,title,file_name,stored_name,mime_type,file_size) VALUES (?,?,?,?,?,?)';
    $st=Database::conn()->prepare($sql);
    $st->execute([$data['folder_id'],$data['title'],$data['file_name'],$data['stored_name'],$data['mime_type'],$data['file_size']]);
    return (int)Database::conn()->lastInsertId();
  }

  public static function all(): array {
    $sql='SELECT r.*,f.name folder_name FROM resources r LEFT JOIN folders f ON f.id=r.folder_id ORDER BY r.id DESC';
    return Database::conn()->query($sql)->fetchAll();
  }

  public static function byFolder(int $folderId): array {
    $sql='SELECT r.*,f.name folder_name FROM resources r LEFT JOIN folders f ON f.id=r.folder_id WHERE r.folder_id=? ORDER BY r.id DESC';
    $stmt=Database::conn()->prepare($sql);
    $stmt->execute([$folderId]);
    return $stmt->fetchAll();
  }

  public static function find(int $id) {
    $stmt=Database::conn()->prepare('SELECT * FROM resources WHERE id=? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function updateMeta(int $id, string $title, ?int $folderId = null): void {
    Database::conn()->prepare('UPDATE resources SET title=?, folder_id=? WHERE id=?')->execute([$title, $folderId, $id]);
  }

  public static function delete(int $id): void {
    Database::conn()->prepare('DELETE FROM resources WHERE id=?')->execute([$id]);
  }

  public static function assignedToStaff(int $staffId): array {
    $sql='SELECT DISTINCT r.*, f.name folder_name FROM resources r
      INNER JOIN resource_staff rs ON rs.resource_id=r.id
      LEFT JOIN folders f ON f.id=r.folder_id
      WHERE rs.staff_id=? ORDER BY r.id DESC';
    $stmt=Database::conn()->prepare($sql); $stmt->execute([$staffId]);
    return $stmt->fetchAll();
  }

  public static function assignedToStaffByFolder(int $staffId, int $folderId): array {
    $sql='SELECT DISTINCT r.*, f.name folder_name FROM resources r
      INNER JOIN resource_staff rs ON rs.resource_id=r.id
      LEFT JOIN folders f ON f.id=r.folder_id
      WHERE r.folder_id=? AND rs.staff_id=?
      ORDER BY r.id DESC';
    $stmt=Database::conn()->prepare($sql);
    $stmt->execute([$folderId,$staffId]);
    return $stmt->fetchAll();
  }

  public static function canStaffAccess(int $staffId, int $resourceId): bool {
    $sql='SELECT COUNT(*) c FROM resources r
      INNER JOIN resource_staff rs ON rs.resource_id=r.id
      WHERE r.id=? AND rs.staff_id=?';
    $st=Database::conn()->prepare($sql); $st->execute([$resourceId,$staffId]);
    return (int)$st->fetch()['c'] > 0;
  }
}
