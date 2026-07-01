# Reporting Engine Specification

This document details the generation, aggregation, and export mechanisms for daily, weekly, monthly, and employee-specific reports.

---

## 1. Aggregation Logic & SQL Queries

The Reporting Engine runs on demand or via scheduled tasks, aggregating raw database logs into structured reports.

### 1.1 Daily Summary Query
Aggregates attendance metrics for a specific day:
```sql
SELECT 
    e.employee_id, 
    CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
    MIN(CASE WHEN a.clock_type = 'CHECK_IN' THEN a.clock_time END) AS clock_in,
    MAX(CASE WHEN a.clock_type = 'CHECK_OUT' THEN a.clock_time END) AS clock_out,
    a.status AS final_status
FROM employees e
LEFT JOIN attendance_logs a ON e.id = a.employee_id AND DATE(a.clock_time) = :target_date
WHERE e.status = 'ACTIVE'
GROUP BY e.id, e.employee_id, e.first_name, e.last_name, a.status;
```

### 1.2 Monthly Aggregate Reporting
Aggregates monthly attendance parameters (e.g., total present days, late count, total overtime minutes):
* **Present Days**: Count of days with at least one check-in event.
* **Late Count**: Count of logs marked `LATE`.
* **Total Overtime**: Sum of calculated overtime minutes across the billing period.

---

## 2. Export Format Definitions

```
┌───────────────────────────────────────────────────────────────┐
│                        EXPORT ENGINES                         │
├───────────────────────┬──────────────────────┬────────────────┤
│      PDF Engine       │      CSV Engine      │  Excel Engine  │
│                       │                      │                │
│ • Custom styling      │ • Raw flat format    │ • Multi-sheet  │
│ • Multi-page support  │ • Streaming responses│ • Formatted cells│
│ • Corporate branding  │ • Low memory footprint│• Formulas (Sum)│
└───────────────────────┴──────────────────────┴────────────────┘
```

### 2.1 PDF Engine (ReportLab / WeasyPrint)
* **Structure**: PDF reports must include:
  * **Header**: Company Logo, Name, Report Title, and Date Range.
  * **Summary Section**: Dashboard overview of attendance metrics (e.g., Average Attendance Rate).
  * **Data Table**: Columns: Date, Employee Name, Check-in, Check-out, and Status. Alternating row colors improve readability.
  * **Footer**: Page numbers and generated timestamp.

### 2.2 Excel Export (openpyxl)
* **Workbook Structure**:
  * **Sheet 1**: "Overview" - Summarized dashboard metrics.
  * **Sheet 2**: "Roster Detail" - Raw, daily clock-in/out records.
* **Formatting Rules**:
  * Auto-adjust column widths to prevent text truncation.
  * Apply green background fills for `PRESENT` statuses, orange for `LATE` or `EARLY_LEAVE`, and red for `ABSENT`.

### 2.3 CSV Export
* **Structure**: A single, unformatted sheet containing raw text records.
* **Headers**: `employee_id, employee_name, date, check_in, check_out, status, total_hours, overtime_minutes`
* **Performance**: The backend streams CSV lines directly to the client response to minimize server memory usage.

For settings and configurations, refer to [11_SETTINGS.md](11_SETTINGS.md).
