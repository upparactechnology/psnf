@echo off
cd /d "%~dp0"
echo Starting FastAPI Face Recognition Backend on port 8000...
python -m uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload
if %errorlevel% neq 0 (
    echo.
    echo Attempting run with uvicorn direct command...
    uvicorn app.main:app --host 0.0.0.0 --port 8000 --reload
)
pause
