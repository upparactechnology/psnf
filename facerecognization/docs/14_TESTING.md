# Quality Assurance and Testing Framework

This document outlines the testing strategy, performance benchmarks, recognition accuracy verification, and manual verification checklists.

---

## 1. Automated Test Architecture

```
┌───────────────────────────────────────────────────────────────┐
│                        AUTOMATED TESTS                        │
├───────────────────────┬──────────────────────┬────────────────┤
│      Unit Tests       │  Integration Tests   │ Android UI Test│
│                       │                      │                │
│ • PyTest (Backend)    │ • DB transactions    │ • Espresso UI  │
│ • Kotlin Unit Tests   │ • Mock client calls  │ • CameraX mock │
│ • Business logic validation • Flow assertions │ • Network mock │
└───────────────────────┴──────────────────────┴────────────────┘
```

### 1.1 Backend Unit Testing (PyTest)
* **Goal**: Validate business rules, schemas, and processing functions in isolation.
* **Database Mocking**: Use SQLite in-memory databases for unit testing to avoid writing to the production database.

### 1.2 Integration Testing
* **API Route Checks**: Verify end-to-end API request and response cycles.
* **Flow Verification**: Assert that logging attendance triggers the proper updates in shift and log tables.

### 1.3 Android UI Testing (Espresso)
* **View Mocking**: Verify screen transitions and loading states.
* **Camera Analysis Test**: Feed mock frames into the ImageAnalysis pipeline to verify liveness and detection states.

---

## 2. Face Recognition Accuracy Validation (FAR/FRR)

To verify the accuracy of the face recognition engine, use the following validation metrics:

| Metric | Target Value | Definition |
| :--- | :--- | :--- |
| **FAR** (False Acceptance Rate) | $< 0.001\%$ | The rate at which the system incorrectly matches an unauthorized face. |
| **FRR** (False Rejection Rate) | $< 1.0\%$ | The rate at which the system fails to match an authorized face. |
| **Liveness Accuracy** | $> 99.0\%$ | Accuracy in identifying spoofing attempts. |

---

## 3. Load and Stress Benchmarks

* **Concurrency Targets**: Support up to 50 concurrent requests/second on the backend matching service.
* **Stress Threshold**: Cosine matching performance must remain stable with database volumes up to 10,000 face templates.

---

## 4. Manual Testing Checklist

Before deploying updates to production, execute the following manual tests:

### 4.1 Device Enrollment Tests
- [ ] Position face in the center of the camera and verify straight detection.
- [ ] Turn head left, right, up, and down to verify pose guidance.
- [ ] Blink eyes to confirm blink verification.
- [ ] Verify that final face embeddings are saved to the database.

### 4.2 Liveness Verification Tests
- [ ] Hold up a printed photograph of an employee to verify spoof detection.
- [ ] Play a high-definition video of an employee on a smartphone screen to verify spoof detection.
- [ ] Verify that spoof attempts are logged in the dashboard.

### 4.3 Offline Sync Verification Tests
- [ ] Disconnect the tablet from Wi-Fi.
- [ ] Scan an employee to verify that a successful check-in is logged locally.
- [ ] Reconnect the tablet to Wi-Fi and verify that the cached log is synced to the backend server.

For details on long-term system scaling plans, refer to [15_FUTURE_ROADMAP.md](15_FUTURE_ROADMAP.md).
