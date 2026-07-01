# Configuration and Settings Specification

This document details global configurations, recognition parameters, local hardware options, and backup procedures.

---

## 1. Global System Configuration Matrix

Global settings are stored in the database's `settings` table as key-value pairs and cached in backend application memory.

| Key | Data Type | Default Value | Description |
| :--- | :--- | :--- | :--- |
| `company_name` | String | "My Company" | Custom branding title used in header areas. |
| `company_logo` | URL / Path | "/assets/logo.png"| Custom branding logo used in exports. |
| `recognition_threshold` | Float | `0.65` | Minimum similarity score required for face matches. |
| `duplicate_cooldown_minutes` | Integer | `5` | Time window to ignore duplicate scans. |
| `voice_prompt_enabled` | Boolean | `true` | Toggles client audio feedback. |
| `liveness_threshold` | Float | `0.85` | Minimum confidence score required to pass liveness checks. |

---

## 2. Kiosk-Specific Hardware Configurations

The Android client maintains settings locally using secure preferences:
* **Camera Selection**: Toggles between front-facing camera (default for wall kiosk configurations) and rear-facing camera.
* **Camera Resolution**: Sets capture resolution (default: $640 \times 480$ at 30 FPS).
* **Voice Prompt Selection**: Selects voice feedback languages (e.g., English, Spanish).

---

## 3. Data Backup and Restore Operations

```
                   BACKUP SCHEDULE (DAILY CRON)
                                │
                       Execute mysqldump
                                │
                    Compress file (.sql.gz)
                                │
                    Has upload succeeded?
                           ╱         ╲
                          ╱           ╲
                        YES            NO
                        ╱               ╲
               Save in local storage    Log critical alert
             & clean logs (> 30 days)   to admin dashboard
```

### 3.1 Automated Backup Routine
A daily cron job runs on the Ubuntu VPS to back up the MySQL database:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/attendance"
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="attendance_backup_${DATE}.sql.gz"

# Export and compress database
mysqldump -u root -p'Password' attendance_db | gzip > "${BACKUP_DIR}/${FILENAME}"

# Retain backups for 30 days
find "${BACKUP_DIR}" -type f -mtime +30 -name "*.sql.gz" -exec rm {} \;
```

### 3.2 Restore Procedure
In the event of a system failure, administrators can restore the database from a backup file:
```bash
gunzip < /var/backups/attendance/attendance_backup_20260625_000000.sql.gz | mysql -u root -p'Password' attendance_db
```

For security policies and encryption standards, refer to [12_SECURITY.md](12_SECURITY.md).
