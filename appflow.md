# Application Flow & Navigation Maps

This document maps user paths, screen states, and logical redirects for key workflows using Mermaid flowcharts.

---

## 1. Global Navigation Architecture

The entry points and high-level routing flow for all user roles:

```mermaid
graph TD
    Entry([Start URL]) --> Login[Login Page]
    Login --> AuthCheck{Credential Verification}
    AuthCheck -- Valid Admin/Manager --> AdminDash[Admin Dashboard]
    AuthCheck -- Valid Teacher/Staff/Driver --> Launcher[Module Launcher Dashboard]
    AuthCheck -- Valid Parent --> ParentPortal[Parent Portal Home]
    AuthCheck -- Invalid --> LoginAlert[Error Alert / Rate Limit Increment]

    Launcher --> |Permission Checked| LaunchModule[Load Module Workspace]
    LaunchModule --> SidebarNav[Render Sidebar Navigation]
```

---

## 2. Admin Workspace Workflow (Folder & Resource Management)

How the administrator assigns materials and secures the platform:

```mermaid
graph TD
    Start[Admin Dashboard] --> NavFiles[Manage Folders & Resources]
    NavFiles --> CreateFolder[Create Folder / Subfolder]
    NavFiles --> UploadFile[Upload File to storage/private]

    UploadFile --> RecordDB[Write Metadata to 'resources' Table]
    RecordDB --> AssignStaff[Open Modal: Select Assignee Staff members]
    AssignStaff --> SaveAssign[Write to 'folder_staff' & 'resource_staff' Join Tables]

    Start --> NavSettings[System Settings Panel]
    NavSettings --> ConfigLocks[Configure Time Windows & Device Toggles]
    ConfigLocks --> SaveSettings[Save key-values to 'settings' table]
```

---

## 3. Teacher/Therapist Workflow (IEP & Class Logging)

The daily work loop for special education educators:

```mermaid
graph TD
    Start[Teacher Dashboard] --> SelectClass[Select Target Group/Class]
    SelectClass --> NavAtt[Mark Attendance Grid]
    NavAtt --> SelectReason[Select Disability-aware Reason if Absent]
    SelectReason --> SaveAtt[Submit Attendance to DB]

    SelectClass --> StudentProfile[Open Student Diagnostic Profile]
    StudentProfile --> ViewMed[View Allergy & Incident Logs]
    StudentProfile --> UpdateIEP[Modify IEP Goals & Milestones]
    StudentProfile --> GameStats[View Educational Game telemetry scores]
```

---

## 4. Parent Portal Flow

How parents interact with transportation tracking, medical logs, and progress reports:

```mermaid
graph TD
    Start[Parent Portal Home] --> NavBus[Live Bus Tracker]
    NavBus --> StreamGPS[Stream Coordinates via OpenStreetMap Leaflet layer]

    Start --> NavIncidents[View Incident Alerts]
    NavIncidents --> ReadMedLog[Inspect medication times and nurse logs]

    Start --> NavReports[Academic & Progress Center]
    NavReports --> ReadIEP[View Teacher Comments & IEP completion ratings]
    ReadIEP --> PlayStats[Inspect game activity details for Money/Signs games]
```

---

## 5. Staff DRM Flow (Secure Material Review)

The secure loop enforcing viewing blocks and activity auditing:

```mermaid
graph TD
    Start[Staff Portal Home] --> CheckTime{Is Current Time within Settings access windows?}
    CheckTime -- No --> AccessBlocked[Deny login / Kill Active Session]
    CheckTime -- Yes --> ShowFolders[Browse Assigned Folders]

    ShowFolders --> SelectResource[Click Target Resource]
    SelectResource --> CheckAuth{Is resource assigned to user?}
    CheckAuth -- No --> AccessDenied[Show 403 Forbidden Page]
    CheckAuth -- Yes --> GenerateStream[Generate 15-minute Expiring Signature Token]

    GenerateStream --> RunController[Call Controller: /staff/resource/stream]
    RunController --> AuditLog[Write event 'resource_viewed' to activity_logs]
    RunController --> RenderPreview[Render Wrapper Viewport Container]

    subgraph DRM View Layer
        RenderPreview --> Webhooks[Inject JS blocking CTRL+P / PrintScreen / ContextMenu]
        RenderPreview --> Watermark[Overlay Dynamic User Metadata SVGs]
        RenderPreview --> BlurHook[Listen for onblur event -> CSS blur filter applied]
    end
```
