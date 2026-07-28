<?php
namespace App\Models;

use App\Core\Database;

class User {
  public static function adminByEmail(string $email) {
    $stmt = Database::conn()->prepare('
      SELECT u.* 
      FROM users u 
      JOIN user_roles ur ON ur.user_id = u.id 
      JOIN roles r ON r.id = ur.role_id 
      WHERE u.email = ? AND u.is_active = 1 AND r.slug IN (\'super_admin\', \'school_admin\', \'manager\') 
      LIMIT 1
    ');
    $stmt->execute([$email]);
    return $stmt->fetch();
  }

  public static function staffByEmail(string $email) {
    $stmt = Database::conn()->prepare('SELECT * FROM staff WHERE email=? AND is_active=1 LIMIT 1');
    $stmt->execute([$email]);
    return $stmt->fetch();
  }

  public static function staffById(int $id) {
    $stmt = Database::conn()->prepare('SELECT id,name,email,is_active FROM staff WHERE id=? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch();
  }

  public static function allStaff(): array {
    return Database::conn()->query('SELECT id,name,email,is_active FROM staff ORDER BY id DESC')->fetchAll();
  }

  public static function createStaff(string $name, string $email, string $password): void {
    $sql = 'INSERT INTO staff (name,email,password,is_active) VALUES (?,?,?,1)';
    Database::conn()->prepare($sql)->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT)]);
  }

  public static function updateStaff(int $id, string $name, string $email): void {
    Database::conn()->prepare('UPDATE staff SET name=?, email=? WHERE id=?')->execute([$name, $email, $id]);
  }

  public static function deleteStaff(int $id): void {
    Database::conn()->prepare('DELETE FROM staff WHERE id=?')->execute([$id]);
  }

  public static function updateStaffProfile(int $id, string $name, string $password = ''): void {
    if ($password !== '') {
      $hash = password_hash($password, PASSWORD_BCRYPT);
      Database::conn()->prepare('UPDATE staff SET name=?, password=? WHERE id=?')->execute([$name, $hash, $id]);
      return;
    }
    Database::conn()->prepare('UPDATE staff SET name=? WHERE id=?')->execute([$name, $id]);
  }

  public static function setStaffStatus(int $id, int $active): void {
    Database::conn()->prepare('UPDATE staff SET is_active=? WHERE id=?')->execute([$active, $id]);
  }

  public static function resetStaffPassword(int $id, string $newPassword): void {
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    Database::conn()->prepare('UPDATE staff SET password=? WHERE id=?')->execute([$hash, $id]);
  }
}
