# Face Recognition & Verification Pipeline

This document details the real-time face detection, alignment, liveness validation, vector matching, and performance optimization rules.

---

## 1. Frame Processing Pipeline

```
  Camera Stream (YUV) ──► RGBA Buffer ──► Resize ──► RetinaFace Detection ──► Landmark Alignment
                                                                                    │
                                                                           Liveness Score >= 0.85?
                                                                                   ╱ ╲
                                                                                  ╱   ╲
                                                                                YES    NO ──► Reject
                                                                                ╱
                                                                               ╱
                                                                    Extract ArcFace Embedding
                                                                               │
                                                                     Calculate Similarity
                                                                       (Cosine >= 0.65)
```

The real-time recognition pipeline operates on frame cycles:
1. **Resolution Scale**: Scale the camera stream to $640 \times 480$ pixels to optimize inference performance.
2. **RetinaFace Detection**: Extract bounding box coordinates and 5 facial landmark points (eyes, nose, mouth corners).
3. **Face Alignment**: Apply an affine transformation using the landmark coordinates to center and align the face.

---

## 2. Liveness Detection Engine (Anti-Spoofing)

To prevent spoofing attempts (e.g., holding up a printed photo or playing high-definition video on a screen), the system implements multi-tiered liveness checks:

```
┌────────────────────────────────────────────────────────────────────────┐
│                        LIVENESS CHECKS                                 │
├───────────────────────┬──────────────────────┬─────────────────────────┤
│    Texture Analysis   │  Frequency Screening │     Temporal Blink      │
│                       │                      │                         │
│ • Micro-texture check │ • Moire pattern check│ • Eye aspect ratio (EAR)│
│ • Paper edge detection│ • Screen reflections │ • Voluntary blink track │
└───────────────────────┴──────────────────────┴─────────────────────────┘
```

### 2.1 Micro-Texture Analysis
An ONNX-based lightweight liveness network checks micro-textures and reflections. Real skin has organic diffuse reflection properties, whereas paper and screens introduce flat surfaces, unnatural gloss, or distinct borders.

### 2.2 Frequency Analysis (Moire Pattern Protection)
Uses Fast Fourier Transform (FFT) analysis on the face bounding box to detect high-frequency repeating Moire patterns, which indicate a camera recording of an LCD screen.

### 2.3 Temporal Blink Detection (Voluntary Liveness)
Measures the **Eye Aspect Ratio (EAR)** across successive frames:
$$\text{EAR} = \frac{\|p_2 - p_6\| + \|p_3 - p_5\|}{2 \|p_1 - p_4\|}$$
* **Threshold**: An EAR drop below `0.2` followed by a recovery back to `0.35` within 1 second validates a physical eye blink.

---

## 3. Matching Thresholds & Vector Similarity

Face recognition uses **Cosine Similarity** to compare captured face embeddings against registered templates:

$$\text{Similarity}(A, B) = \frac{A \cdot B}{\|A\| \|B\|}$$

* **Recommended Threshold**: $0.65$.
  * **Similarity $\ge 0.65$**: Profile matched.
  * **Similarity $< 0.65$**: Profile rejected (unidentified).
* **Multi-Template Matching**: Compare the query embedding against all templates assigned to an employee. If any template similarity scores $\ge 0.65$, it registers as a match. The highest score determines the match identity.

---

## 4. Multi-Face and Performance Optimization

### 4.1 Multiple Face Handling
If multiple faces are detected in a frame (e.g., employees standing in a queue):
1. **Primary Target Selection**: Calculate the area of all face bounding boxes.
2. **Focus Target**: Select only the face with the largest bounding box area (which represents the person closest to the camera).
3. **Ignore Others**: Discard and bypass processing for all other detected faces in the frame.

### 4.2 Performance Optimizations
* **Skip Frames**: Run detection and verification on every 3rd frame (approx. 10 FPS) instead of every frame, reducing CPU usage without affecting response times.
* **Thread Offloading**: Run CameraX preview tasks on the main UI thread, while moving face detection and embedding calculations to a dedicated background thread pool.

For details on the Android application screens and integration, refer to [06_ANDROID_APP.md](06_ANDROID_APP.md).
