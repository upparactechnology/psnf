# Android Application Specification

This document details the screens, architecture, offline sync mechanisms, and configuration details for the native Kotlin **Android Kiosk Application**.

---

## 1. Core Screen Layouts

The application must support the following screens, optimized for landscape tablet devices:

```
┌───────────────────────────────────────────────────────────────┐
│                      KIOSK CAMERA STREAM                      │
├───────────────────────────────┬───────────────────────────────┤
│                               │   STATUS / NOTIFICATION BOX   │
│                               │                               │
│       [ CAMERA VIEW ]         │   • Welcome John Doe          │
│    • Oval Target Overlay      │   • Status: CHECK_IN          │
│    • Real-time Face Tracker   │   • Timestamp: 09:00 AM       │
│                               │                               │
└───────────────────────────────┴───────────────────────────────┘
```

### 1.1 Splash Screen
* **Purpose**: Initial loading screen to verify permissions, check configuration states, and load the local SQLite database cache.
* **Flow**: If a valid server configuration and auth token exist, route to the **Kiosk Live Camera** screen. Otherwise, route to the **Login Screen**.

### 1.2 Login Screen
* **Purpose**: Authenticates system administrators.
* **Fields**: Host URL (e.g., `https://attendance.company.com`), Admin Username, and Admin Password.
* **Security**: Saves the host URL and JWT tokens securely in Android EncryptedSharedPreferences.

### 1.3 Kiosk Live Camera Screen
* **Purpose**: The primary screen for daily operation, capturing face inputs.
* **UI Components**:
  * Full-screen CameraX preview window with an oval overlay indicating the target face position.
  * Side panel showing the current time, connection status (Online/Offline), and local sync queue size.
  * Status overlay: Shows a green card with name/photo for success, or a red card for errors or unrecognized faces.

### 1.4 Admin Management Panel
* **Purpose**: Allows authorized admins to enroll new employees or trigger manual synchronization.
* **UI Components**:
  * Employee list with enrollment statuses.
  * Settings menu to adjust recognition thresholds, toggle voice prompts, and configure camera selections.

---

## 2. Technical Architecture & Libraries

The app follows the MVVM pattern to separate concerns and improve testability:

```
┌────────────────────────────────────────────────────────────────────────┐
│                              ANDROID APP                               │
├───────────────────────┬──────────────────────┬─────────────────────────┤
│     UI Views (M3)     │      ViewModel       │       Repository        │
│                       │                      │                         │
│ • CameraActivity      │ • KioskViewModel     │ • AttendanceRepository  │
│ • EnrollmentFragment  │ • LiveData / Flows   │ • Local Room Database   │
│ • Material 3 Styles   │ • State Machine      │ • Retrofit REST Client  │
└───────────────────────┴──────────────────────┴─────────────────────────┘
```

* **CameraX API**: Uses the `ImageAnalysis` analyzer tool, converting frames to `InputImage` buffers for local face detection.
* **Room Database**: Caches employee names and face templates locally to support offline verification.
* **Coroutines & Flow**: Manages asynchronous operations (such as camera frame rendering and network requests) on background threads.

---

## 3. Offline Caching & Synchronization Logic

The application supports offline verification to maintain operations during network outages:

```
                 REAL-TIME RECOGNITION (OFFLINE)
                               │
                       Face recognized?
                              ╱ ╲
                             ╱   ╲
                           YES    NO
                           ╱       ╲
                 Save attendance    Discard / Alert
                 log in Room DB
                 (is_synced = false)
                               │
                     Network restored?
                              ╱ ╲
                             ╱   ╲
                           YES    NO ───► Stay Offline
                           ╱
               Upload cached logs to server
                (is_synced = true)
```

1. **Local Logging**: When the device is offline, successful match events are saved to the local Room database with `is_synced = false`.
2. **Network Monitoring**: A network connectivity listener monitors connection states.
3. **Queue Sync**: When network connectivity is restored, a WorkManager task pushes cached logs to the backend API (`POST /attendance/sync`) and marks them as synced.

---

## 4. Kiosk Mode & Device Controls

To prevent tampering, the application must run in a locked kiosk configuration:
* **Screen Pinning**: Configured using DevicePolicyManager to lock the device to the attendance application.
* **System Bar Lockout**: Disables status bar expansion, notification drawer access, and navigation buttons (Home/Back).
* **Auto-Launch**: Sets the application to auto-launch on device boot, ensuring continuous availability.

For details on the web administration panels and dashboards, refer to [07_ADMIN_DASHBOARD.md](07_ADMIN_DASHBOARD.md).
