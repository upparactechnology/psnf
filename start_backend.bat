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
echo Launching Uvicorn Server at http://localhost:8000 ...
python -m uvicorn app:app --host 0.0.0.0 --port 8000 --reload

pause
