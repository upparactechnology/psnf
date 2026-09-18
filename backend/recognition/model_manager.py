import gc
import time
import threading
from typing import Optional, Callable, Any
from utils.logger import logger


class ModelManager:
    """
    Kiosk-aware lazy-loading model manager with face-presence-based lifecycle.

    State machine:
      IDLE       — model unloaded, Haar-only monitoring
      ACTIVE     — InsightFace loaded, detection + recognition available

    The model unloads when no face has been detected for NO_FACE_IDLE_TIMEOUT.
    Browser camera frames do NOT keep the model alive — only actual face
    presence (via mark_face_present) resets the no-face timer.

    Thread-safe. Never calls unload() while holding _lock.
    """

    def __init__(self):
        self._model: Any = None
        self._lock = threading.Lock()
        self._last_face_detected: Optional[float] = None
        self._model_loaded_at: Optional[float] = None
        self._no_face_idle_timeout: int = 300
        self._load_fn: Optional[Callable] = None

        self._stop_event = threading.Event()
        self._unloader: Optional[threading.Thread] = None

    def configure(self, idle_timeout: int, no_face_idle_timeout: int, load_fn: Callable):
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
        """Returns the InsightFace model, loading it lazily on first call."""
        with self._lock:
            if self._model is None:
                if self._load_fn is None:
                    raise RuntimeError("Model loader is not configured")

                logger.info("Loading InsightFace model...")
                self._model = self._load_fn()
                self._model_loaded_at = time.monotonic()
                self._last_face_detected = time.monotonic()
                logger.info("InsightFace model loaded successfully")

            return self._model

    def unload(self, reason: str = "manual"):
        """Unload the InsightFace model and release memory."""
        model_to_release = None

        with self._lock:
            if self._model is not None:
                model_to_release = self._model
                self._model = None
                self._last_face_detected = None
                self._model_loaded_at = None

        if model_to_release is not None:
            del model_to_release
            gc.collect()
            logger.info(f"InsightFace model unloaded ({reason})")

    def is_loaded(self) -> bool:
        with self._lock:
            return self._model is not None

    def mark_face_present(self):
        """
        Call when a face is detected in the current frame.
        Resets the no-face idle timer. Must be called from detector
        when either Haar or InsightFace finds >= 1 face.
        """
        with self._lock:
            self._last_face_detected = time.monotonic()

    def mark_no_face(self):
        """
        Call when InsightFace detects zero faces in the current frame.
        Does NOT immediately unload — the auto-unloader thread checks
        the timeout and decides when to unload.
        """
        pass

    def face_detected(self):
        """Backward-compatible alias for mark_face_present()."""
        self.mark_face_present()

    def _auto_unload(self):
        """
        Background thread that unloads the model when no face has been
        detected for NO_FACE_IDLE_TIMEOUT seconds.

        Only fires when the model IS loaded and no face is present.
        Never calls unload() while holding _lock.
        """
        while not self._stop_event.wait(30):
            reason = None

            with self._lock:
                if self._model is None or self._model_loaded_at is None:
                    continue

                if self._last_face_detected is None:
                    continue

                no_face_time = time.monotonic() - self._last_face_detected

                if no_face_time >= self._no_face_idle_timeout:
                    reason = f"no face detected for {no_face_time:.0f}s"

            # Lock released — safe to call unload()
            if reason:
                self.unload(reason=reason)


model_manager = ModelManager()
