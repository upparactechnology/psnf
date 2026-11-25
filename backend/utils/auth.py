"""
Shared-secret authentication between the PHP proxy (public/attendance/api.php)
and this FastAPI service.

Every request except GET /health must carry the header

    X-Internal-Key: <key>

The key is read from env FACE_API_KEY, or from the file
<repo root>/storage/keys/face_api_key (raw string, whitespace stripped).
If neither exists the file is generated (secrets.token_hex(32)).
"""
import hmac
import os
import secrets
import time
from typing import Optional

from utils.logger import logger

HEADER_NAME = "X-Internal-Key"

_BACKEND_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
REPO_ROOT    = os.path.dirname(_BACKEND_DIR)
KEY_FILE     = os.path.join(REPO_ROOT, "storage", "keys", "face_api_key")

_cached_key: Optional[str] = None


def _read_key_file() -> str:
    with open(KEY_FILE, "r", encoding="utf-8") as fh:
        return fh.read().strip()


def _load_or_create_key() -> str:
    env_key = (os.getenv("FACE_API_KEY") or "").strip()
    if env_key:
        return env_key

    os.makedirs(os.path.dirname(KEY_FILE), exist_ok=True)

    if not os.path.exists(KEY_FILE):
        new_key = secrets.token_hex(32)
        try:
            # O_EXCL: only one creator wins if PHP/Python race to create the file
            fd = os.open(KEY_FILE, os.O_WRONLY | os.O_CREAT | os.O_EXCL, 0o600)
            with os.fdopen(fd, "w", encoding="utf-8") as fh:
                fh.write(new_key)
            logger.info("Generated new internal API key file.")
            return new_key
        except FileExistsError:
            pass  # someone else created it — read theirs below

    # The other writer may not have flushed yet; retry briefly on an empty read.
    for _ in range(20):
        key = _read_key_file()
        if key:
            return key
        time.sleep(0.05)
    raise RuntimeError("Internal API key file exists but is empty.")


def get_api_key() -> str:
    global _cached_key
    if _cached_key is None:
        _cached_key = _load_or_create_key()
    return _cached_key


def is_valid_key(provided: Optional[str]) -> bool:
    if not provided:
        return False
    try:
        expected = get_api_key()
    except Exception as exc:  # pragma: no cover
        logger.error(f"Could not load internal API key: {exc}")
        return False
    return hmac.compare_digest(provided.strip().encode("utf-8"), expected.encode("utf-8"))
