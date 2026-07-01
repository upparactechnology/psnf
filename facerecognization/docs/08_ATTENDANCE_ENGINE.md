# Attendance Engine Specification

This document details the business logic, rules, and calculations implemented in the backend **Attendance Engine** to process raw clock-in/out events and determine daily attendance statuses.

---

## 1. Event Classification Logic

When a face is recognized at a kiosk device, the system logs a raw clock event. The **Attendance Engine** dynamically classifies this event as a `CHECK_IN` or `CHECK_OUT` based on active shifts and existing logs:

```
                      CLOCK-IN EVENT INGESTION
                                 │
                   Has employee clocked in today?
                                ╱ ╲
                               ╱   ╲
                             YES    NO
                             ╱       ╲
                            ╱         ╲
                  Classify as         Classify as
                  `CHECK_OUT`          `CHECK_IN`
```

1. **First Log of the Day**: If there are no attendance logs for the employee on the target working day, the first event is classified as `CHECK_IN`.
2. **Subsequent Logs**: Any subsequent event logged by the employee on the same working day updates the `CHECK_OUT` timestamp.
3. **Midnight Shift Handling**: For shifts that span across midnight (e.g., Night Shift 22:00 to 06:00), the working day is determined by the shift start date rather than the calendar date of the clock event.

---

## 2. Duplicate Prevention (Cooldown Guard)

To prevent accidental double logs when an employee stands in front of the kiosk after a successful scan, the Android client and backend enforce a strict **cooldown window**:

* **Default Interval**: 5 minutes (`300 seconds`).
* **Client Enforcement**: The Android app locks recognition processing for the recognized ID during the cooldown period.
* **Server Verification**: The server discards incoming match requests for the same employee ID if the timestamp is within 5 minutes of their last logged event.

---

## 3. Status Classification Rules

An employee's daily status is determined by comparing their clock times against their assigned shift schedule.

### 3.1 Status Matrix

```
       SHIFT START                                           SHIFT END
            │                                                    │
 ┌──────────┴───────────┬───────────────────────────┬────────────┴─────────┐
 │       Grace          │          Working          │        Early         │
 │       Period         │          Window           │        Leave         │
 └──────────────────────┴───────────────────────────┴──────────────────────┘
 ◄─────────────────────►                            ◄─────────────────────►
        On Time                                              Early
```

| Status | Trigger Condition | Code Implementation Formula |
| :--- | :--- | :--- |
| **PRESENT** | Check-in occurs within the grace period. | `CheckInTime <= ShiftStart + GracePeriod` |
| **LATE** | Check-in occurs after the grace period. | `CheckInTime > ShiftStart + GracePeriod` |
| **EARLY_LEAVE** | Check-out occurs before the shift end time. | `CheckOutTime < ShiftEnd` |
| **HALF_DAY** | Total active hours worked are less than the half-day threshold. | `TotalHoursWorked < HalfDayMinutes` |
| **ABSENT** | No clock-in is registered by the end of the shift. | `No Log by ShiftEnd + GracePeriod` |

---

## 4. Work Hours & Overtime Calculations

### 4.1 Working Hours Formula
Total active hours are calculated by finding the difference between check-out and check-in times, excluding configured breaks:
$$\text{Total Hours} = \frac{\text{CheckOutTime} - \text{CheckInTime}}{60} - \text{BreakDurationMinutes}$$

### 4.2 Overtime (OT) Rules
* **Minimum Threshold**: Overtime must exceed 30 minutes to be registered.
* **Overtime Calculation**: Calculated as the difference between check-out time and shift end time:
  $$\text{Overtime Minutes} = (\text{CheckOutTime} - \text{ShiftEnd}) \ge 30$$

### 4.3 Missing Check-Outs
If a `CHECK_IN` is registered but no corresponding `CHECK_OUT` occurs before the day-boundary check (typically run at 04:00 AM on the following day):
1. The engine marks the log status as `INCOMPLETE`.
2. Total working hours for that day default to `0`.
3. An administrative flag is raised, requiring manual correction in the Admin Dashboard.

---

## 5. Administrative Corrections

Administrators can manually adjust logs to resolve anomalies (e.g., forgotten scans or incorrect statuses):
* **Audit Trail Requirements**: Any manual adjustment must record the Admin User ID, a change reason, the original values, and the modified values.
* **Recalculation**: Saving a manual correction triggers the engine to recalculate work hours and statuses for that record.

For shift configuration templates and rotating schedules, refer to [09_SHIFT_MANAGEMENT.md](09_SHIFT_MANAGEMENT.md).
