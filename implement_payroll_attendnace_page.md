Below is the detailed implementation plan.

PSNF Payroll & Attendance — Detailed Implementation Plan
1. Core Business Rules

These should become the single source of truth for the implementation.

1.1 Attendance vs Salary Days

Do not use one working_days concept for both systems.

Create two concepts:

Attendance Working Day
Salary Working Day

Example:

Monday-Friday → Attendance + Salary
Saturday      → Attendance + Salary
Sunday        → No Attendance + Salary
Holiday       → No Attendance + Salary, if paid holiday

The exact non-Sunday attendance schedule can continue to follow the school's configured working-day policy.

1.2 Sundays

Sundays:

Employee does not need to clock in.
No absence should be generated.
No late calculation.
No checkout requirement.
Sunday is included in salary-day calculation.

So the current fallback behavior of excluding Sundays from working days must be changed for payroll. The existing documentation currently says Sundays are excluded.

2. Separate Attendance Calendar from Salary Calendar

Create a payroll calendar resolver.

Attendance calendar

Determines:

Should employee be expected to attend?

Possible results:

ATTENDANCE_REQUIRED
SUNDAY_OFF
HOLIDAY
Salary calendar

Determines:

Does this date count in the salary denominator?

Possible results:

SALARY_DAY
PAID_HOLIDAY

This prevents the current Sunday conflict.

3. Salary-Day Calculation

For each payroll month:

Salary Days =
all calendar days
- unpaid/non-payable days, if your policy has any

Under your current rule:

Sunday = included
Paid holiday = included

Example:

August 2026
31 calendar days


Salary Days = 31

If August contains a paid holiday:

Salary Days = 31

because the employee is still paid for that holiday.

Daily salary
Daily Salary =
Basic Salary / Salary Days

Example:

Basic Salary = ₹31,000
Salary Days = 31


Daily Salary = ₹1,000
4. Attendance Status Engine

Before payroll calculation, generate a normalized daily attendance result for every employee.

Possible statuses:

PRESENT
HALF_DAY
ABSENT
PAID_LEAVE
UNPAID_LEAVE
SUNDAY
PAID_HOLIDAY

Additional fields:

check_in
check_out
working_hours
source
is_late
late_exempted
incomplete_log
5. Attendance Source Priority

For each employee/date:

Manual Attendance
       ↓
Kiosk Attendance
       ↓
Approved Leave
       ↓
Absent

But holidays/Sundays should be resolved before absence.

Recommended resolution:

1. Is it Sunday?
      → SUNDAY


2. Is it a paid holiday?
      → PAID_HOLIDAY


3. Is there manual attendance?
      → use MANUAL


4. Is there kiosk attendance?
      → use KIOSK


5. Is there approved paid leave?
      → PAID_LEAVE


6. Is there approved unpaid leave?
      → UNPAID_LEAVE


7. Otherwise
      → ABSENT

Manual attendance must override kiosk attendance for calculation, but kiosk data should remain available for audit.

6. Paid Leave

Approved paid leave:

Attendance status = PAID_LEAVE
Salary deduction = ₹0

It remains part of salary days.

Example:

Salary Days = 31
Paid Leave = 2

Still:

Daily Salary = Basic / 31

Do not reduce salary days to 29.

7. Unpaid Leave

Approved unpaid leave:

Attendance status = UNPAID_LEAVE
Deduction = 1 daily salary

Example:

Basic = ₹31,000
Salary Days = 31
Daily = ₹1,000


Unpaid Leave = 2


Deduction = ₹2,000
8. Leave + Actual Attendance

If an employee has approved leave but actually attends:

Approved Leave
+
Kiosk/Manual Attendance

Attendance wins.

The leave record should not be deleted.

Change its state to something like:

RECALLED

or:

RETURNED_TO_WORK

Keep:

recalled_at
recalled_by
recalled_reason

for audit purposes.

9. Normal Half-Day

This is separate from the late penalty.

If the employee actually arrives after the half-day threshold:

attendance_status = HALF_DAY

Normal half-day deduction:

Daily Salary × 50%

Example:

Daily Salary = ₹1,000


Normal Half-Day Deduction = ₹500

This should be stored separately from late penalty deductions.

10. Late Calculation

This is the most important change.

Count only actual late clock-ins

For the payroll month:

Effective Late Count =
all late attendance records
- exempted late records

Late count is:

Monthly.
Non-consecutive.
Not reset after a penalty.
Not dependent on consecutive days.
Not affected by whether the next day is late.
11. Late Exemption

Admins must be able to exempt individual late records.

Example:

Date: 10 Aug
Late: Yes
Reason: School duty
Exempted: Yes

That record:

does NOT contribute to late_count

Recommended fields:

late_exempted
late_exemption_reason
late_exempted_by
late_exempted_at
12. Exact Late Penalty Rule

Your final rule is:

Every 3 accumulated non-exempt late marks causes the NEXT eligible salary day to be treated as a half-day for salary calculation.

The employee does not need to be late on the penalty day.

Example
Late 1 → count = 1
Late 2 → count = 2
Late 3 → count = 3


NEXT eligible salary day
→ 50% salary

Then:

Late 4 → count = 4
Late 5 → count = 5
Late 6 → count = 6


NEXT eligible salary day
→ another 50% salary

Then:

Late 7
Late 8
Late 9


NEXT eligible salary day
→ another 50% salary

So:

3 late  → 1 penalty
6 late  → 2 penalties
9 late  → 3 penalties
12 late → 4 penalties
13. Critical Difference: Penalty Is Not Applied to the Late Day

This must be explicitly implemented.

Example:

Monday → Late #3
Tuesday → Employee arrives ON TIME

Tuesday becomes:

Salary treatment = HALF DAY

but Tuesday's attendance remains:

PRESENT

The system must not change Tuesday's attendance status to HALF_DAY.

Instead:

Attendance:
PRESENT


Payroll:
Late Penalty Half-Day

This distinction is extremely important.

14. Late Penalty Allocation Algorithm

The payroll engine should process salary days chronologically.

Conceptually:

effective_late_count = 0
pending_penalties = 0


for each attendance-required day:


    determine attendance


    if employee is late and not exempted:
        effective_late_count++


        if effective_late_count % 3 == 0:
            pending_penalties++


    apply pending penalty to the NEXT eligible salary day

But there is an important implementation detail:

Do not penalize the same day that creates the 3rd late.

For example:

Day 5 → Late #3

Day 5 remains normal salary.

Then:

Day 6 → next eligible salary day

Day 6 receives the half-day salary penalty.

15. What Is an "Eligible Salary Day"?

For your requirement, the penalty should move to the next day on which salary can meaningfully be reduced.

Recommended:

Eligible:
Normal salary day
Paid holiday
Sunday


Not eligible:
Employee's employment start not begun
Employee's employment ended

However, because Sunday has no attendance, the payroll engine must decide whether a Sunday can receive a penalty.

Based on your latest requirement, I recommend:

Sunday is a salary day
BUT
late penalty should move to the next attendance-required salary day.

Example:

Friday → 3rd late
Saturday → next salary day → penalty

If Saturday is not an attendance day under your school's schedule:

Sunday → salary day but no attendance
Monday → penalty

This prevents an invisible Sunday from receiving an attendance-derived penalty.

16. Late Penalty and Existing Half-Day

If the penalty day is already a normal half-day:

Do not accidentally deduct another 50% unless your business policy explicitly wants that.

Recommended rule:

Normal Half-Day = 50%
Late Penalty Half-Day = 50%
Maximum salary treatment for one day = 100%

Example:

Daily salary = ₹1,000


Normal half-day = ₹500
Late penalty = ₹500


Total reduction = ₹1,000

So the employee can receive:

₹0 for that salary day

but never:

₹1,500 deduction

for a ₹1,000 daily salary.

This should be enforced in the calculation engine.

17. One-Scan Rule

Keep the current behavior:

One scan = PRESENT

No absence deduction.

But mark:

incomplete_log = 1

Display:

Incomplete attendance — checkout missing.

The current system already uses earliest scan as check-in and latest scan as checkout.

18. Payroll Data Structure

I recommend expanding payroll_items.

employee_id


salary_basic
salary_days
daily_salary


present_days
normal_half_days
absent_days
paid_leave_days
unpaid_leave_days
paid_holiday_days
sunday_days


late_count
late_exempted_count
late_penalty_half_days


absent_deduction
unpaid_leave_deduction
normal_half_day_deduction
late_penalty_deduction


total_deductions
net_salary
19. Do NOT Merge Half-Day Counters

Keep:

normal_half_days

and:

late_penalty_half_days

separate.

Example:

Present              23
Normal Half-Day       1
Absent                1
Paid Leave            2


Late Marks            6
Late Exemptions       1
Late Penalty Days     2

This makes the payslip understandable.

20. Payroll Formula
Daily salary
Daily Salary =
Basic Salary / Salary Days
Absence
Absent Deduction =
Absent Days × Daily Salary
Unpaid leave
Unpaid Leave Deduction =
Unpaid Leave Days × Daily Salary
Normal half-day
Normal Half-Day Deduction =
Normal Half-Day Count × Daily Salary × 50%
Late penalty
Late Penalty Deduction =
Late Penalty Half-Days × Daily Salary × 50%
Total
Total Deduction =
Absent Deduction
+ Unpaid Leave Deduction
+ Normal Half-Day Deduction
+ Late Penalty Deduction
Net
Net Salary =
Basic Salary - Total Deduction

Keep the existing basic-salary deduction cap if that remains your approved policy. The current documentation specifies a cap at basic salary.

21. Example Full Payroll

Assume:

Basic Salary = ₹31,000
Salary Days = 31
Daily Salary = ₹1,000

Employee:

Present = 24
Normal Half-Day = 1
Absent = 2
Paid Leave = 3
Late Marks = 6
Late Exemptions = 0
Late Penalty Half-Days = 2

Deductions:

Absent:
2 × ₹1,000 = ₹2,000


Normal Half-Day:
1 × ₹500 = ₹500


Late Penalty:
2 × ₹500 = ₹1,000

Total:

₹2,000 + ₹500 + ₹1,000
= ₹3,500

Net:

₹31,000 - ₹3,500
= ₹27,500
22. Holidays Table

Create:

holidays

Suggested schema:

id
holiday_date
name
type
is_paid
status
created_at
updated_at

Examples:

2026-08-15 | Independence Day | public | 1
2026-08-27 | School Holiday   | school | 1

Payroll should automatically load these dates.

23. Sunday Handling in Database/Engine

Do not create fake attendance records for Sundays.

Instead:

attendance table:
No Sunday record required

Payroll calendar:

Sunday:
salary_day = 1
attendance_required = 0

This is much cleaner than inserting fake check-in/check-out records.

24. Payroll Regeneration

Once payroll is generated:

APPROVED

Attendance/leave changes should not silently change it.

If something changes:

APPROVED
   ↓
OUT_OF_SYNC

Show:

Payroll has changes in attendance/leave data.


[ Regenerate Payroll ]

Regeneration should recalculate everything from the current source data.

25. Out-of-Sync Triggers

Mark payroll OUT_OF_SYNC when:

Attendance modified
Manual attendance added
Manual attendance deleted
Late exemption added/removed
Leave approved
Leave rejected
Leave recalled
Holiday added/modified
Employee salary changed
Shift rule changed

But only if the affected date/month belongs to that payroll run.

26. Audit Trail

Record:

Payroll Generated
Payroll Regenerated
Attendance Override
Late Exempted
Leave Recalled
Holiday Added
Payroll Out-of-Sync

For every override:

user_id
action
entity
entity_id
old_value
new_value
reason
timestamp
27. Payslip UI

The payslip should show:

Attendance
Salary Days             31
Present Days             24
Normal Half-Days          1
Absent Days               2
Paid Leave                3
Unpaid Leave              0
Late
Late Marks                6
Excused Late Marks        0
Late Penalty Half-Days    2
Salary
Basic Salary          ₹31,000
Deductions
Absent                 ₹2,000
Normal Half-Day          ₹500
Late Penalty           ₹1,000
--------------------------------
Total Deduction        ₹3,500
Net
NET SALARY             ₹27,500
28. Admin Payroll Details

Admin should additionally see which dates received late penalties.

Example:

Late Marks:


05 Aug — Late #1
08 Aug — Late #2
12 Aug — Late #3


13 Aug — LATE PENALTY HALF-DAY


17 Aug — Late #4
20 Aug — Late #5
24 Aug — Late #6


25 Aug — LATE PENALTY HALF-DAY

This is extremely useful when an employee disputes salary.

29. Important Edge Cases

These must be decided in code.

Case A — 3rd late on Friday
Friday = Late #3
Saturday = next eligible salary day

Apply penalty according to the school's attendance calendar.

Case B — 3rd late before Sunday
Friday = Late #3
Sunday = salary day but no attendance
Monday = next attendance-required salary day

Recommended: penalty on Monday.

Case C — 3rd late before holiday
Monday = Late #3
Tuesday = Paid Holiday
Wednesday = penalty day

Recommended: penalty Wednesday.

Case D — Employee leaves before penalty day

If employment ends after the 3rd late and before the next eligible salary day:

The payroll engine should not create a phantom penalty day after employment ends.

Case E — Late is subsequently exempted

If payroll was already generated:

Approved Payroll
       ↓
Late exemption added
       ↓
OUT_OF_SYNC

Regeneration removes the applicable penalty.

30. Development Order

I recommend implementing in this exact order:

Step 1 — Database
 Create holidays
 Add late-exemption fields
 Add attendance source
 Add incomplete-log flag
 Add payroll breakdown fields
 Add payroll sync/status fields
 Add audit fields/tables
Step 2 — Calendar Engine
 Create salary-day resolver
 Include Sundays in salary days
 Don't require Sunday attendance
 Add paid holiday handling
 Separate attendance calendar from salary calendar
Step 3 — Attendance Engine
 Manual > kiosk
 Kiosk fallback
 Leave fallback
 Sunday handling
 Holiday handling
 One-scan handling
 Incomplete attendance detection
Step 4 — Leave Engine
 Paid leave
 Unpaid leave
 Leave + attendance priority
 Recall/return-to-work status
 Audit trail
Step 5 — Late Engine
 Calculate monthly late count
 Exclude exempted late records
 Accumulate late marks
 Every 3 late → pending salary penalty
 Apply penalty to next eligible attendance-required salary day
 Don't modify actual attendance status
 Keep penalty counter separate
Step 6 — Payroll Engine
 Calculate salary days
 Calculate daily salary
 Calculate absences
 Calculate unpaid leave
 Calculate normal half-days
 Calculate late penalty half-days
 Apply deduction cap
 Calculate net salary
Step 7 — Payslip
 Separate attendance summary
 Separate late summary
 Separate deduction reasons
 Show penalty dates
Step 8 — Sync
 Detect changes
 Mark payroll OUT_OF_SYNC
 Add Regenerate button
 Recalculate safely
 Maintain regeneration history
Step 9 — Testing
 1 late
 2 late
 3 late
 4th salary day penalty
 6 late → 2 penalties
 9 late → 3 penalties
 Exempted late
 Sunday salary
 Sunday no attendance
 Holiday salary
 Paid leave
 Unpaid leave
 Leave + scan
 Manual + kiosk
 One scan
 Normal half-day + late penalty
 Payroll regeneration
Final architecture
                    ┌──────────────────┐
                    │ Salary Calendar  │
                    │                  │
                    │ Sundays = PAID   │
                    │ Holidays = PAID  │
                    └────────┬─────────┘
                             │
                             ▼
┌─────────────┐      ┌──────────────────┐
│ Kiosk Scan  │─────▶│                  │
└─────────────┘      │ Attendance Engine│
                     │                  │
┌─────────────┐      │ Manual > Kiosk   │
│Manual Entry │─────▶│ Leave > Absent   │
└─────────────┘      └────────┬─────────┘
                              │
┌─────────────┐               │
│ Leave       │───────────────┤
└─────────────┘               │
                              ▼
                     ┌─────────────────┐
                     │ Late Engine     │
                     │                 │
                     │ 3 Late Marks    │
                     │       ↓         │
                     │ Next eligible   │
                     │ salary day      │
                     │ = 50% salary    │
                     └────────┬────────┘
                              │
                              ▼
                     ┌─────────────────┐
                     │ Payroll Engine  │
                     │                 │
                     │ Salary Days     │
                     │ Absence         │
                     │ Leave           │
                     │ Half-Day        │
                     │ Late Penalty    │
                     └────────┬────────┘
                              │
                              ▼
                     ┌─────────────────┐
                     │ Payroll Run     │
                     │                 │
                     │ APPROVED        │
                     │ OUT_OF_SYNC     │
                     └────────┬────────┘
                              │
                              ▼
                     ┌─────────────────┐
                     │ Payslip         │
                     └─────────────────┘
One rule to make absolutely explicit to the developer

Late marks affect payroll only. They do not change attendance records. After every 3 non-exempt late clock-ins accumulated during the salary month, the next eligible attendance-required salary day receives a 50% salary deduction, even if the employee arrives on time that day. The count is cumulative, non-consecutive, and continues throughout the month. Sundays are not attendance days, but Sundays are included in the salary-day denominator.