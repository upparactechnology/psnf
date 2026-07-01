# AI Prompt: Face AI & Verification Pipeline

Use this prompt to instruct an AI assistant to implement the Face AI module:

```markdown
## Objective
Implement the Face AI processing pipeline—including face detection, landmark alignment, liveness verification, and ArcFace embedding extraction—using InsightFace, OpenCV, and ONNX Runtime.

## Requirements
1. **Model Loading (ONNX)**:
   - Implement an initializer to load the RetinaFace model for face detection and landmark identification.
   - Load the ArcFace (`buffalo_l` or similar) model for extracting 512-dimensional face vectors.
   - Load a lightweight liveness model for micro-texture and screen reflection check.
2. **Inference Pipeline**:
   - **Step 1: Face Detection**: Process frames to locate faces, extract bounding box coordinates, and identify 5 landmark points (eyes, nose, mouth corners).
   - **Step 2: Landmark Alignment**: Apply an affine transformation to center and align the face.
   - **Step 3: Liveness Check**: Run the aligned face through the liveness model. If the liveness confidence score falls below `0.85`, reject the frame.
   - **Step 4: Embedding Extraction**: Run the aligned face through the ArcFace model to generate a 512-dimensional embedding vector.
3. **Similarity Calculations**:
   - Compare embedding vectors using Cosine Similarity:
     $$\text{Similarity}(A, B) = \frac{A \cdot B}{\|A\| \|B\|}$$
   - Return matches for similarity scores $\ge 0.65$.

## Target Folder Structure
```
backend/
└── app/
    └── ai/
        ├── __init__.py
        ├── pipeline.py                 # Core AI inference pipeline class
        ├── liveness.py                 # Liveness classification logic
        └── models/                     # Directory for pre-trained ONNX models
            ├── detection.onnx
            ├── recognition.onnx
            └── liveness.onnx
```

## Coding Standards
- Optimize processing speed by running ONNX Runtime inference sessions using native thread pools.
- Handle exceptions safely (e.g., return empty arrays or scores of 0 on failed frame preprocessing or when no faces are detected).

## Expected Deliverables
1. Core AI inference implementation (`pipeline.py`).
2. Liveness detection code (`liveness.py`).

## Acceptance Criteria
- Running pipeline unit tests against sample images (containing photos, spoof screens, and actual faces) correctly classifies liveness states and outputs accurate similarity matches.
```
