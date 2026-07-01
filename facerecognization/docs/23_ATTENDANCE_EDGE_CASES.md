# Attendance Edge Case Resolution Guide

This document defines how the system handles operational anomalies, environmental disruptions, scheduling edge cases, and physical scanning variations.

---

## 1. Operational & Connectivity Edge Cases

```
┌───────────────────────────────────────────────────────────────┐
│                    CONNECTIVITY DISRUPTIONS                   │
├───────────────────────┬──────────────────────┬────────────────┤
│    Internet Outage    │   Camera Offline     │ Power Outage   │
│                       │                      │                │
│ • Local Room DB cache │ • Log hardware error │ • Kiosk down   │
│ • Offline status UI   │ • Display red camera │ • UPS backup   │
│ • Auto-sync on restore│   icon overlay       │ • Check Room queue│
└───────────────────────┴──────────────────────┴────────────────┘
```

### 1.1 Kiosk Internet Outage
* **Problem**: The Android client loses network connection, failing to connect to the backend server.
* **Android Behavior**: Switches the recognition matching engine to query the local Room database cache. Displays a red "Offline" status indicator in the top bar.
* **Database Action**: Saves successful matches to the local Room database with `is_synced = false`.
* **Sync Recovery**: Once network connection is restored, a background task pushes the cached logs to the backend API (`POST /attendance/sync`) and marks them as synced.

---

## 2. Scheduling & Shift Edge Cases

### 2.1 Forgotten Check-Out
* **Problem**: An employee clocks in at their shift start but does not clock out at their shift end.
* **Backend Logic**: A nightly job running at 04:00 AM checks for open check-in logs. It flags these records as `INCOMPLETE` and defaults the day's total working hours to `0`.
* **UI Response**: The Admin Dashboard flags the record with a warning icon, prompting the administrator to manually add the missing check-out time.

### 2.2 Cross-Midnight Shifts (Night Shifts)
* **Problem**: An employee's shift begins on one calendar day and ends on the next (e.g., 22:00 to 06:00).
* **Backend Logic**: The Attendance Engine maps clock events occurring within 4 hours of the shift end (before 10:00 AM on the second day) to the working day on which the shift began.

---

## 3. Physical Scan Variations

### 3.1 Face Coverings (Glasses, Masks, Beards)
* **Problem**: The employee wears glasses, a face mask, or grows a beard, which can affect matching accuracy.
* **Expected Behavior**: Landmark alignment scales parameters based on the eye and nose bridge coordinates, allowing recognition even if the mouth area is obscured by a mask.
* **Recovery Strategy**: If similarity scores fall below the matching threshold ($0.65$), the system prompts the employee to remove their mask or glasses and scan again.

### 3.2 Identical Twins
* **Problem**: Two employees are identical twins, resulting in similar face embeddings.
* **Expected Behavior**: ArcFace embeddings are highly discriminative, but identical twins may generate similarity scores that exceed the matching threshold ($0.65$).
* **Recovery Strategy**: Set a higher, custom matching threshold (e.g., $0.75$) for the twins' profiles, or require PIN verification in addition to face scans for those employees.

For details on the hardware targets and server specifications, refer to [24_SUPPORTED_DEVICES.md](24_SUPPORTED_DEVICES.md).
