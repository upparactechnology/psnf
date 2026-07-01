# Shift and Schedule Management

This document defines how the system handles shifts, rotating schedules, holiday policies, and weekend settings.

---

## 1. Shift Classifications

The system supports two primary shift types to accommodate different operational needs:

```
┌───────────────────────────────────────────────────────────────┐
│                          SHIFT TYPES                          │
├───────────────────────────────┬───────────────────────────────┤
│          Fixed Shift          │        Flexible Shift         │
│                               │                               │
│ • Rigid Core Hours            │ • Hours-Based Targets         │
│ • Defined Grace Windows       │ • Dynamic Clock-in Window     │
│ • Auto-categorized Late Status│ • Status based on hours worked│
└───────────────────────────────┴───────────────────────────────┘
```

### 1.1 Fixed Shifts
* **Description**: Traditional shifts with fixed start and end boundaries (e.g., Morning Shift: 09:00 - 18:00).
* **Late Rules**: Employees are marked `LATE` if check-in exceeds the start time plus grace period.
* **Early Leave**: Employees are marked `EARLY_LEAVE` if check-out occurs before the end time.

### 1.2 Flexible Shifts
* **Description**: Shifts defined by a target duration (e.g., 8 hours per day) rather than fixed core hours.
* **Rules**: Employees can clock in at any time. Late status is not calculated; attendance status depends on completing the target work duration.

---

## 2. Shift Profiles (Default Configurations)

The system includes pre-defined shift profiles:

| Profile Name | Start Time | End Time | Grace Period | Half-Day Threshold | Purpose |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Morning Shift** | 09:00 | 18:00 | 15 minutes | 240 minutes | Standard office shift |
| **Evening Shift** | 14:00 | 22:00 | 15 minutes | 240 minutes | Operational second shift |
| **Night Shift** | 22:00 | 06:00 (Next Day) | 10 minutes | 240 minutes | Overnight operations |
| **Flexible Day** | Flex (06:00 - 22:00) | Duration: 8 hours | N/A | 240 minutes | Remote / Core developers |

---

## 3. Shift Assignment & Rotating Schedules

Shifts can be assigned to employees in two ways:
1. **Static Shift Assignment**: The employee is mapped to a single shift profile (e.g., Morning Shift) indefinitely.
2. **Rotating Rosters**: Assigned using the `employee_shifts` mapping table. For example:
   * **Week 1**: Morning Shift.
   * **Week 2**: Evening Shift.
   * **Rule Check**: When processing a clock event, the Attendance Engine checks the `employee_shifts` table for the shift active on that date.

---

## 4. Weekend and Holiday Policies

### 4.1 Holiday Calculations
* **System Settings**: Admin can register national, regional, or company-specific holidays.
* **Engine Action**: On designated holidays, the system automatically logs employees as `HOLIDAY` status. If an employee clocks in on a holiday, the event is logged as `PRESENT` and flagged as holiday overtime.

### 4.2 Weekend Configurations
* **Weekly Off Days**: Configurable at the department or company level (default: Saturday and Sunday).
* **Auto-Absent Exclusions**: The daily overnight absenteeism cron job ignores designated rest days.

For details on report generation and export formats, refer to [10_REPORTS.md](10_REPORTS.md).
