import cv2
import numpy as np
import os
import onnxruntime as ort

class FaceAIPipeline:
    def __init__(self, model_dir: str):
        self.model_dir = model_dir
        self.detector_session = None
        self.embedding_session = None
        
        det_path = os.path.join(model_dir, "detection.onnx")
        rec_path = os.path.join(model_dir, "recognition.onnx")
        
        # Initialize ONNX sessions if files exist
        opts = ort.SessionOptions()
        opts.intra_op_num_threads = 2
        opts.inter_op_num_threads = 1
        
        if os.path.exists(det_path):
            self.detector_session = ort.InferenceSession(det_path, sess_options=opts, providers=['CPUExecutionProvider'])
        if os.path.exists(rec_path):
            self.embedding_session = ort.InferenceSession(rec_path, sess_options=opts, providers=['CPUExecutionProvider'])

    def check_blur(self, gray_img: np.ndarray) -> float:
        """
        Calculates sharpness using Laplacian operator variance.
        Returns a float value. Higher means sharper.
        """
        return cv2.Laplacian(gray_img, cv2.CV_64F).var()

    def check_brightness(self, rgb_img: np.ndarray) -> float:
        """
        Calculates average brightness (Y channel of YUV color space).
        """
        yuv = cv2.cvtColor(rgb_img, cv2.COLOR_RGB2YUV)
        return float(np.mean(yuv[:, :, 0]))

    def align_face(self, frame: np.ndarray, landmarks: np.ndarray) -> np.ndarray:
        """
        Applies affine transformation to center and align the face.
        """
        # Define reference points for aligned face coordinates (112x112 target size)
        coord_ref = np.array([
            [30.2946, 51.6963],
            [65.5318, 51.5014],
            [48.0252, 71.7366],
            [33.5493, 92.3655],
            [62.7299, 92.2041]
        ], dtype=np.float32)
        
        # Compute affine transformation matrix
        M, _ = cv2.estimateAffinePartial2D(landmarks, coord_ref)
        if M is None:
            return cv2.resize(frame, (112, 112))
            
        aligned = cv2.warpAffine(frame, M, (112, 112))
        return aligned

    def extract_embedding(self, aligned_face: np.ndarray) -> np.ndarray:
        """
        Runs the aligned face through the recognition model to extract a 512-dim embedding vector.
        """
        if not self.embedding_session:
            # Mock placeholder to run without models locally
            return np.random.randn(512).astype(np.float32)
            
        # Preprocessing: resize, normalize, transpose to CHW
        img = cv2.resize(aligned_face, (112, 112))
        img = img.astype(np.float32)
        img = (img - 127.5) / 127.5  # ArcFace input scale
        img = np.transpose(img, (2, 0, 1))
        img = np.expand_dims(img, axis=0)
        
        input_name = self.embedding_session.get_inputs()[0].name
        output_name = self.embedding_session.get_outputs()[0].name
        
        outputs = self.embedding_session.run([output_name], {input_name: img})
        embedding = outputs[0][0]
        
        # Normalize embedding to unit length (L2 Normalization)
        norm = np.linalg.norm(embedding)
        if norm > 0:
            embedding = embedding / norm
        return embedding

    @staticmethod
    def calculate_cosine_similarity(emb1: np.ndarray, emb2: np.ndarray) -> float:
        """
        Calculates similarity between two embedding vectors.
        """
        emb1 = np.array(emb1)
        emb2 = np.array(emb2)
        norm1 = np.linalg.norm(emb1)
        norm2 = np.linalg.norm(emb2)
        if norm1 == 0 or norm2 == 0:
            return 0.0
        return float(np.dot(emb1, emb2) / (norm1 * norm2))
