# Face Enrollment Workflow Specification

This document details the multi-angle face enrollment pipeline, frame filtering rules, embedding extraction, and security standards for registering new employee profiles.

---

## 1. Enrollment Workflow

```mermaid
flowchart TD
    Start[Admin Initiates Enrollment] --> CameraSelect[Select Camera: Front or Rear]
    CameraSelect --> GuideUi[Show Oval HUD / Face Boundary Overlay]
    GuideUi --> Record[Record 10-15 Second Frame Sequence]
    
    subgraph Guide Prompts
        P1[1. Look Straight] --> P2[2. Turn Head Left]
        P2 --> P3[3. Turn Head Right]
        P3 --> P4[4. Tilt Head Up]
        P4 --> P5[5. Tilt Head Down]
        P5 --> P6[6. Blink Eyes]
    end
    
    Record --> Process[Process Video / Frame Collection]
    Process --> Filter[Run Image Quality & Blur Checks]
    Filter --> GenEmbed[Generate 512-dim Embeddings]
    GenEmbed --> Save[Save Embeddings to Database]
```

1. **Admin Authorization**: The Admin logs into the system on the Android tablet or Admin Dashboard, selects an employee, and taps **Start Enrollment**.
2. **Camera Selection**: The system defaults to the camera designated for attendance. It is highly recommended to use the same physical device and camera module for both enrollment and attendance to ensure consistent lighting and lens characteristics.
3. **Oval HUD UI Overlay**: Displays a guided oval overlay on screen to assist the employee with positioning.
4. **Recording & Audio Prompts**: Captures frames over a 10–15 second window. Voice prompts or on-screen instructions guide the user through different angles.

---

## 2. Dynamic Posing Guidance Sequence

The system prompts the user to rotate their head through specific orientations to capture facial features under varying angles:

| Step | On-Screen Prompt | Audio Output | Duration | Required Pitch/Yaw Range |
| :--- | :--- | :--- | :--- | :--- |
| **1** | Look Straight | "Please look straight into the camera." | 2 seconds | Pitch: $[-5^\circ, 5^\circ]$, Yaw: $[-5^\circ, 5^\circ]$ |
| **2** | Turn Left | "Slowly turn your head to the left." | 2 seconds | Yaw: $[-30^\circ, -15^\circ]$ |
| **3** | Turn Right | "Slowly turn your head to the right." | 2 seconds | Yaw: $[15^\circ, 30^\circ]$ |
| **4** | Look Up | "Tilt your head up slightly." | 2 seconds | Pitch: $[15^\circ, 25^\circ]$ |
| **5** | Look Down | "Tilt your head down slightly." | 2 seconds | Pitch: $[-25^\circ, -15^\circ]$ |
| **6** | Blink | "Blink your eyes." | 1 second | Eye Closeness Ratio $\le 0.2$ |

---

## 3. Frame Quality & Selection Engine

The system analyzes captured frames in real-time to select the best templates and discard low-quality inputs.

```
                  RAW IMAGE FRAME INGESTION
                              │
                    Meets lighting range?
                           (50-250)
                            ╱ ╲
                           ╱   ╲
                         YES    NO ───► Discard Frame
                         ╱
                        ╱
              Meets sharpness threshold?
                     (Laplacian > 80)
                        ╱ ╲
                       ╱   ╲
                     YES    NO ───► Discard Frame
                     ╱
                    ╱
           Is face occluded / masked?
                        ╱ ╲
                       ╱   ╲
                     YES    NO ───► Discard Frame
                     ╱
           Keep frame for embeddings
```

### 3.1 Brightness Filtering
Calculates the mean pixel intensity ($Y$ channel in YUV space).
* **Threshold**: Must fall within $[50, 250]$. Frames that are too dark ($<50$) or overexposed ($>250$) are discarded.

### 3.2 Sharpness (Blur Detection)
Applies the **Laplacian Variance** method to measure image focus.
* **Algorithm**: Compute the Laplacian operator on the grayscale image, then calculate its variance ($\sigma^2$).
* **Threshold**: $\sigma^2 \ge 80.0$. Frames falling below this limit are discarded.

### 3.3 Occlusion Checks
InsightFace landmark coordinates verify that both eyes, nose, and mouth are visible. If landmark confidence scores fall below `0.85` (indicating occlusions like masks, heavy shadows, or hands), the frame is discarded.

---

## 4. Embedding Generation & Security

* **Target Frame Selection**: The quality engine selects the top 5 frames from the sequence (maximizing sharpness and pose variation).
* **Embedding Extraction**: The ArcFace model extracts a 512-dimensional floating-point vector from each selected frame.
* **Storage Standard**: Embeddings are stored as serializable float arrays in the database. Storing raw face images is prohibited to protect user privacy.

For details on the real-time matching pipeline and liveness detection rules, refer to [05_FACE_RECOGNITION.md](05_FACE_RECOGNITION.md).
