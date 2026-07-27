CREATE DATABASE IF NOT EXISTS psnf_drm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE psnf_drm;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE staff (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE folders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parent_id INT NULL,
  name VARCHAR(190) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE SET NULL
);

CREATE TABLE resources (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folder_id INT NULL,
  title VARCHAR(190) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL,
  mime_type VARCHAR(190) NOT NULL,
  file_size BIGINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL
);

CREATE TABLE folder_staff (
  folder_id INT NOT NULL,
  staff_id INT NOT NULL,
  PRIMARY KEY(folder_id, staff_id),
  FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

CREATE TABLE resource_staff (
  resource_id INT NOT NULL,
  staff_id INT NOT NULL,
  PRIMARY KEY(resource_id, staff_id),
  FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
);

CREATE TABLE staff_favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT NOT NULL,
  resource_id INT NULL,
  folder_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
  FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
  FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_staff_resource (staff_id, resource_id),
  UNIQUE KEY uniq_staff_folder (staff_id, folder_id)
);

CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(120) UNIQUE NOT NULL,
  value TEXT NULL
);

CREATE TABLE activity_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NULL,
  staff_id INT NULL,
  event VARCHAR(190) NOT NULL,
  meta TEXT NULL,
  ip VARCHAR(64) NULL,
  user_agent TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(event), INDEX(created_at)
);

INSERT INTO admins (name,email,password) VALUES ('Super Admin','admin@psnf.local','$2y$10$sqGPxsTidqeSFeo7Y6H2ce3KUf20aR5xFDdtJeHrciLBCNOi/92Qi');
INSERT INTO staff (name,email,password) VALUES ('Staff One','staff@psnf.local','$2y$10$sqGPxsTidqeSFeo7Y6H2ce3KUf20aR5xFDdtJeHrciLBCNOi/92Qi');

INSERT INTO settings (`key`,value) VALUES
('deployment_mode','local'),
('global_access_start','01:00'),
('global_access_end','17:00'),
('block_mobile','1'),
('block_laptop','0'),
('allow_smartboard','0'),
('smartboard_min_width','1600'),
('smartboard_min_height','900'),
('access_mon_start','01:00'),
('access_mon_end','17:00'),
('access_tue_start','01:00'),
('access_tue_end','17:00'),
('access_wed_start','01:00'),
('access_wed_end','17:00'),
('access_thu_start','01:00'),
('access_thu_end','17:00'),
('access_fri_start','01:00'),
('access_fri_end','17:00'),
('access_sat_start','01:00'),
('access_sat_end','17:00'),
('access_sun_start','01:00'),
('access_sun_end','17:00'),
('debug_mode','0'),
('download_restriction','1'),
('allowed_file_types','pdf,jpg,jpeg,png,mp4,mov,webm,ppt,pptx,doc,docx,xls,xlsx,txt,zip'),
('max_upload_mb','200');
