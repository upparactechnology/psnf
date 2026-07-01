import numpy as np
import cv2
import onnxruntime as ort
import os

class LivenessDetector:
    def __init__(self, model_path: str):
        self.model_path = model_path
        self.session = None
        if os.path.exists(model_path):
            opts = ort.SessionOptions()
            opts.intra_op_num_threads = 2
            opts.inter_op_num_threads = 1
            self.session = ort.InferenceSession(model_path, sess_options=opts, providers=['CPUExecutionProvider'])

    def predict(self, aligned_face: np.ndarray) -> float:
        """
        Predicts the liveness score of the aligned face image.
        Returns a probability value [0.0, 1.0] where 1.0 is real skin and 0.0 is spoof.
        If model session is not loaded, returns 1.0 default (allowing fallback).
        """
        if not self.session:
            return 1.0
        
        # Preprocess input image (resize, normalize, transposing to CHW format)
        img = cv2.resize(aligned_face, (128, 128))
        img = img.astype(np.float32) / 255.0
        img = np.transpose(img, (2, 0, 1))  # HWC to CHW
        img = np.expand_dims(img, axis=0)   # BCHW
        
        input_name = self.session.get_inputs()[0].name
        output_name = self.session.get_outputs()[0].name
        
        outputs = self.session.run([output_name], {input_name: img})
        # Assume score is probability of the 'real' skin class
        score = float(outputs[0][0][0])
        return score
