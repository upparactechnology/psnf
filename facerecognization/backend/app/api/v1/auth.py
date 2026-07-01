import time
from fastapi import APIRouter, Depends, HTTPException, Request, status
from sqlalchemy.ext.asyncio import AsyncSession
from app.core.database import get_db
from app.core.security import verify_password, create_access_token, create_refresh_token, decode_token
from app.schemas.auth import LoginRequest, TokenResponse, RefreshRequest
from app.crud.auth import get_user_by_username, create_audit_log

router = APIRouter(prefix="/auth", tags=["Authentication"])

@router.post("/login", response_model=TokenResponse)
async def login(request_data: LoginRequest, request: Request, db: AsyncSession = Depends(get_db)):
    request_id = getattr(request.state, "request_id", "unknown")
    user = await get_user_by_username(db, request_data.username)
    if not user or not verify_password(request_data.password, user.password_hash):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect username or password"
        )
    
    access_token = create_access_token(data={"sub": user.username, "role": user.role})
    refresh_token = create_refresh_token(data={"sub": user.username})
    
    # Audit log entry
    await create_audit_log(db, user_id=user.id, action="USER_LOGIN", details=f"Successful login for user: {user.username}")
    
    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
        "expires_in": 3600
    }

@router.post("/refresh", response_model=TokenResponse)
async def refresh_token(request_data: RefreshRequest, request: Request, db: AsyncSession = Depends(get_db)):
    payload = decode_token(request_data.refresh_token)
    if not payload or payload.get("type") != "refresh":
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid or expired refresh token"
        )
    
    username = payload.get("sub")
    user = await get_user_by_username(db, username)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="User not found"
        )
    
    access_token = create_access_token(data={"sub": user.username, "role": user.role})
    new_refresh_token = create_refresh_token(data={"sub": user.username})
    
    return {
        "access_token": access_token,
        "refresh_token": new_refresh_token,
        "token_type": "bearer",
        "expires_in": 3600
    }
