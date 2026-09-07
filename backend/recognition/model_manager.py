import gc
import time
import threading
from typing import Optional, Callable, Any

from utils.logger import logger


class ModelManager:
    """
    Lazy-loading model manager with automatic idle-time unload.

    - Model loads on first get_model() call
    - Tracks last recognition/registration usage separately from detection calls
    - Tracks last face detection — unloads if no face seen for no_face_idle_timeout
    - Background thread unloads model after idle_timeout seconds
    - Thread-safe with proper shutdown via threading.Event
    """

    def __init__(self):
        self._model: Any = None
        self._lock = threading.Lock()
        self._last_used: Optional[float] = None
        self._last_face_detected: Optional[float] = None
        self._model_loaded_at: Optional[float] = None
        self._idle_timeout: int = 600
        self._no_face_idle_timeout: int = 300
        self._load_fn: Optional[Callable] = None

        self._stop_event = threading.Event()
        self._unloader: Optional[threading.Thread] = None

    def configure(self, idle_timeout: int, no_face_idle_timeout: int, load_fn: Callable):
        self._idle_timeout = idle_timeout
        self._no_face_idle_timeout = no_face_idle_timeout
        self._load_fn = load_fn

    def start(self):
        if self._unloader and self._unloader.is_alive():
            return

        self._stop_event.clear()

        self._unloader = threading.Thread(
            target=self._auto_unload,
            daemon=True,
            name="model-auto-unloader"
        )

        self._unloader.start()

    def stop(self):
        self._stop_event.set()

        if self._unloader and self._unloader.is_alive():
            self._unloader.join(timeout=5)

        self.unload(reason="shutdown")

    def get_model(self):
        with self._lock:
            if self._model is None:
                if self._load_fn is None:
                    raise RuntimeError("Model loader is not configured")

                logger.info("Loading InsightFace model...")

                self._model = self._load_fn()
                self._model_loaded_at = time.monotonic()

                logger.info("InsightFace model loaded successfully")

            self._last_used = time.monotonic()

            return self._model

    def unload(self, reason: str = "manual"):
        model_to_release = None

        with self._lock:
            if self._model is not None:
                model_to_release = self._model
                self._model = None
                self._last_used = None
                self._last_face_detected = None
                self._model_loaded_at = None

        if model_to_release is not None:
            del model_to_release
            gc.collect()

            logger.info(
                f"InsightFace model unloaded ({reason})"
            )

    def is_loaded(self) -> bool:
        with self._lock:
            return self._model is not None

    def face_detected(self):
        """Call when a face is actually detected — keeps model alive while scanning."""
        with self._lock:
            self._last_face_detected = time.monotonic()

    def _auto_unload(self):
        while not self._stop_event.wait(30):

            with self._lock:
                if self._model is None or self._model_loaded_at is None:
                    continue

                now = time.monotonic()

                # Unload if no API calls for idle_timeout (model not accessed)
                if self._last_used is not None:
                    idle_time = now - self._last_used
                    if idle_time >= self._idle_timeout:
                        self.unload(reason=f"idle for {idle_time:.0f}s")
                        continue

                # Unload if no face detected for no_face_idle_timeout
                # (kiosk is on but nobody is in front of camera)
                # Use model_loaded_at as baseline if face was never detected
                if self._last_face_detected is not None:
                    no_face_time = now - self._last_face_detected
                else:
                    no_face_time = now - self._model_loaded_at

                if no_face_time >= self._no_face_idle_timeout:
                    self.unload(reason=f"no face detected for {no_face_time:.0f}s")


model_manager = ModelManager()
