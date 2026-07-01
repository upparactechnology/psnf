# AI Prompt: Android Application Development

Use this prompt to instruct an AI assistant to implement the Android client application:

```markdown
## Objective
Develop the native Android Kiosk and Enrollment client application using Kotlin, CameraX, Room database, Material Design 3, and Retrofit.

## Requirements
1. **CameraX & Frame Analysis**:
   - Implement `CameraX` binding inside a landscape-locked activity.
   - Attach an `ImageAnalysis` analyzer to process frames, rendering a bounding box target overlay on-screen.
2. **MVVM UI & Screens (Material 3)**:
   - **Splash Screen**: Checks initial setup and auth state.
   - **Login Screen**: Authenticates system administrators and saves host URL configurations.
   - **Kiosk Camera Screen**: Continuously captures camera streams, processes frame analysis, shows status overlays, and plays voice feedback.
   - **Enrollment Screen**: Guide UI providing pose instructions (Straight, Left, Right, Up, Down, Blink).
3. **Local Database & Offline Cache**:
   - Initialize a local `Room` database containing `Employee` profiles and offline attendance log queues.
   - Perform matching against local profiles when offline. If offline, cache logs with `is_synced = false`.
4. **Sync Worker (WorkManager)**:
   - Schedule a background sync task that uploads cached offline logs (`is_synced = false`) to the backend server (`POST /attendance/sync`) once connection is restored.
5. **Kiosk Hardening**:
   - Implement screen pinning and lock navigation controls.

## Target Folder Structure
```
android/
├── app/
│   ├── src/
│   │   └── main/
│   │       ├── java/com/attendance/
│   │       │   ├── data/
│   │       │   │   ├── local/          # Room DB, entities, DAO interfaces
│   │       │   │   └── remote/         # Retrofit APIs, response schemas
│   │       │   ├── domain/
│   │       │   │   └── sync/           # WorkManager synchronization handler
│   │       │   └── ui/
│   │       │       ├── kiosk/          # Camera view activity and ViewModels
│   │       │       ├── enroll/         # Enrollment fragment UI
│   │       │       └── login/          # Login activity and validation UI
│   │       └── res/
│   │           └── layout/             # M3 XML screen layouts
```

## Coding Standards
- Implement asynchronous network and database operations using Kotlin Coroutines and StateFlow.
- Enforce strict null safety checks across data models.

## Expected Deliverables
1. Complete Android codebase, including Activities, Fragments, ViewModels, Room DAOs, and Layouts.
2. Configuration files (`build.gradle.kts` and `AndroidManifest.xml`).

## Acceptance Criteria
- Building the project in Android Studio succeeds without compilation errors.
- Running the app launches the Kiosk Screen, displaying the camera feed and successfully detecting faces.
```
