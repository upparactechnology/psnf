# System Architecture

Architecture Type:

Modular Monolith

Pattern:

MVC

---

Core

Authentication
RBAC
Module Registry
Notifications
Audit Logs

---

Modules

Students
Parents
Attendance
Timetable
Examinations
Fees
HR
Leave
Payroll
Transport
Medical
Document Designer
Messaging
Announcements
Reports

---

Folder Structure

app/

Core/
Modules/
Shared/

storage/

database/

public/

resources/

routes/

docs/