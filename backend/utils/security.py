import ipaddress
import threading
import time
import jwt
from typing import Optional, Dict
from fastapi import Request, HTTPException, Security, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from config import settings
from utils.logger import logger

security = HTTPBearer(auto_error=False)

# In-memory IP rate limiting: {ip: [timestamps]} — bounded, guarded by a lock
rate_limit_store: Dict[str, list] = {}
_rate_lock = threading.Lock()
_MAX_TRACKED_IPS = 10000
_last_sweep = 0.0

_TRUSTED_PROXIES = {"127.0.0.1", "::1"}


def _valid_ip(value: str) -> Optional[str]:
    try:
        return str(ipaddress.ip_address(value.strip()))
    except ValueError:
        return None


def get_client_ip(request: Request) -> str:
    """
    Returns the peer address. X-Forwarded-For is trusted only when the direct peer
    is the local PHP proxy (127.0.0.1 / ::1).
    """
    peer = request.client.host if request.client else "127.0.0.1"
    if peer in _TRUSTED_PROXIES:
        forwarded = request.headers.get("X-Forwarded-For")
        if forwarded:
            first = _valid_ip(forwarded.split(",")[0])
            if first:
                return first
    return peer


def _sweep(now: float, window: int) -> None:
    """Drop IPs whose newest request is outside the window; hard-cap the store size."""
    global _last_sweep
    if now - _last_sweep < window and len(rate_limit_store) < _MAX_TRACKED_IPS:
        return
    _last_sweep = now
    stale = [ip for ip, ts in rate_limit_store.items() if not ts or now - ts[-1] >= window]
    for ip in stale:
        del rate_limit_store[ip]
    if len(rate_limit_store) >= _MAX_TRACKED_IPS:
        # Still too many: evict the oldest half
        ordered = sorted(rate_limit_store.items(), key=lambda kv: kv[1][-1])
        for ip, _ in ordered[: len(ordered) // 2]:
            del rate_limit_store[ip]


def check_rate_limit(client_ip: str):
    """Enforces windowed rate limiting per client IP."""
    current_time = time.time()
    window = settings.RATE_LIMIT_WINDOW_SECONDS
    max_requests = settings.RATE_LIMIT_REQUESTS

    with _rate_lock:
        _sweep(current_time, window)
        timestamps = [t for t in rate_limit_store.get(client_ip, []) if current_time - t < window]

        if len(timestamps) >= max_requests:
            rate_limit_store[client_ip] = timestamps
            logger.warning(f"Rate limit exceeded for IP: {client_ip}")
            raise HTTPException(
                status_code=status.HTTP_429_TOO_MANY_REQUESTS,
                detail="Rate limit exceeded. Too many requests. Please wait."
            )

        timestamps.append(current_time)
        rate_limit_store[client_ip] = timestamps


def _jwt_secret() -> str:
    if not settings.JWT_SECRET:
        raise RuntimeError("JWT_SECRET is not configured.")
    return settings.JWT_SECRET


def create_access_token(data: dict) -> str:
    """Generates JWT token (requires env JWT_SECRET; currently unused)."""
    return jwt.encode(data.copy(), _jwt_secret(), algorithm=settings.JWT_ALGORITHM)


def verify_jwt_token(credentials: Optional[HTTPAuthorizationCredentials] = Security(security)) -> Optional[dict]:
    """Optional JWT token validator dependency (requires env JWT_SECRET; currently unused)."""
    if not credentials:
        return None
    try:
        return jwt.decode(credentials.credentials, _jwt_secret(), algorithms=[settings.JWT_ALGORITHM])
    except (jwt.PyJWTError, RuntimeError):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid authentication token."
        )
