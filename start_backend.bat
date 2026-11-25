@echo off
title Face Recognition Attendance FastAPI Backend Service (Port 8000)
color 0A

echo =========================================================================
echo  Starting AI Face Recognition Attendance Backend Service on Port 8000
echo =========================================================================
echo.

cd /d "%~dp0backend"

:: Check python installation
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Python is not installed or not added to system PATH.
    echo Please install Python 3.10+ and re-run.
    pause
    exit /b 1
)

echo Installing/Verifying Python dependencies...
pip install -r requirements.txt

echo.
if "%FACE_API_HOST%"=="" set FACE_API_HOST=127.0.0.1

echo Launching Uvicorn Server at http://%FACE_API_HOST%:8000 ...
:: Loopback only by default; no --reload in production. Set FACE_API_HOST to override.
python -m uvicorn app:app --host %FACE_API_HOST% --port 8000

pause
