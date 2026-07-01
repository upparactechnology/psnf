# Face AI Configuration and Optimization Specifications

This document defines the configurations, verification thresholds, quality metrics, and performance optimizations for the face recognition pipeline.

---

## 1. Quality Filters & Pose Limits (Preprocessing)

Before passing a captured face frame to the liveness and embedding engines, it must meet minimum quality thresholds:

```
┌───────────────────────────────────────────────────────────────┐
│                        QUALITY FILTERS                        │
├───────────────────────┬──────────────────────┬────────────────┤
│   Sharpness Filter    │  Brightness Range    │   Pose Limits  │
│                       │                      │                │
│ • Laplacian Variance  │ • Mean Y intensity   │ • Yaw: +/- 20° │
│   threshold >= 80     │   range: [50, 250]   │ • Pitch:+/- 15°│
│ • Discards blur       │ • Discards shadows   │ • Roll: +/- 15°│
└───────────────────────┴──────────────────────┴────────────────┘
```

### 1.1 Laplacian Blur Threshold
* **Metric**: Grayscale image Laplacian operator variance ($\sigma^2$).
* **Value**: $\sigma^2 \ge 80.0$. Frames with $\sigma^2 < 80.0$ are discarded due to motion or focus blur.

### 1.2 Brightness Bounds
* **Metric**: Average pixel intensity of the Y channel in YUV format.
* **Value**: Between $50$ (minimum shadow limit) and $250$ (maximum overexposure limit).

### 1.3 Pose Angle Limits (Euler Angles)
* **Yaw (Side-to-Side)**: Max limit $\pm 20^\circ$.
* **Pitch (Up/Down)**: Max limit $\pm 15^\circ$.
* **Roll (Tilt)**: Max limit $\pm 15^\circ$.
* **Rule**: Frames exceeding these limits are discarded.

---

## 2. Liveness and Matching Configurations

### 2.1 Cosine Matching Threshold
Compare face embeddings using Cosine Similarity:
$$\text{Similarity}(A, B) = \frac{A \cdot B}{\|A\| \|B\|}$$
* **Default Threshold**: $0.65$.
* **Adaptive Thresholding**: If an entryway has poor lighting, the admin can calibrate the threshold between $0.60$ and $0.70$.

### 2.2 Liveness Score Threshold
* **Default Threshold**: $0.85$.
* **FRR/FAR Targets**: Target a False Acceptance Rate (FAR) $<0.001\%$ and a False Rejection Rate (FRR) $<1.0\%$.

---

## 3. ONNX Runtime & Hardware Optimization

To run real-time face analysis efficiently on CPU-only VPS hosting and standard tablet hardware:

### 3.1 Execution Thread Pooling
Configure the ONNX Runtime sessions to prevent CPU thread starvation:
```python
import onnxruntime as ort

opts = ort.SessionOptions()
opts.intra_op_num_threads = 2   # Parallel processing threads within an operator
opts.inter_op_num_threads = 1   # Parallel processing threads across operators
opts.execution_mode = ort.ExecutionMode.ORT_SEQUENTIAL
```

### 3.2 L2 Vector Normalization
Before saving embeddings to the database or running comparison checks, normalize the 512-dimensional output vectors to unit length using L2 normalization:
$$\hat{V} = \frac{V}{\|V\|_2}$$
This simplifies similarity checks to a dot product calculation:
$$\text{Similarity}(A, B) = \hat{A} \cdot \hat{B}$$

For handling system edge cases like twins, masks, or network outages, refer to [23_ATTENDANCE_EDGE_CASES.md](23_ATTENDANCE_EDGE_CASES.md).
