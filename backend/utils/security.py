import time
import jwt
from typing import Optional, Dict
from fastapi import Request, HTTPException, Security, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from config import settings
from utils.logger import logger

security = HTTPBearer(auto_error=False)

# In-memory IP Rate Limiting dictionary: {ip: [timestamps]}
rate_limit_store: Dict[str, list] = {}

def get_client_ip(request: Request) -> str:
    """Extracts client IP address accounting for proxies."""
    forwarded = request.headers.get("X-Forwarded-For")
    if forwarded:
        return forwarded.split(",")[0].strip()
    real_ip = request.headers.get("X-Real-IP")
    if real_ip:
        return real_ip
    return request.client.host if request.client else "127.0.0.1"

def check_rate_limit(client_ip: str):
    """Enforces windowed rate limiting per client IP."""
    current_time = time.time()
    window = settings.RATE_LIMIT_WINDOW_SECONDS
    max_requests = settings.RATE_LIMIT_REQUESTS

    timestamps = rate_limit_store.get(client_ip, [])
    # Keep timestamps within current window
    timestamps = [t for t in timestamps if current_time - t < window]

    if len(timestamps) >= max_requests:
        logger.warning(f"Rate limit exceeded for IP: {client_ip}")
        raise HTTPException(
            status_code=status.HTTP_429_TOO_MANY_REQUESTS,
            detail="Rate limit exceeded. Too many requests. Please wait."
        )

    timestamps.append(current_time)
    rate_limit_store[client_ip] = timestamps

def create_access_token(data: dict) -> str:
    """Generates JWT token."""
    to_encode = data.copy()
    encoded_jwt = jwt.encode(to_encode, settings.JWT_SECRET, algorithm=settings.JWT_ALGORITHM)
    return encoded_jwt

def verify_jwt_token(credentials: Optional[HTTPAuthorizationCredentials] = Security(security)) -> Optional[dict]:
    """Optional JWT token validator dependency for secure routes."""
    if not credentials:
        return None
    token = credentials.credentials
    try:
        payload = jwt.decode(token, settings.JWT_SECRET, algorithms=[settings.JWT_ALGORITHM])
        return payload
    except jwt.PyJWTError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid authentication token."
        )
