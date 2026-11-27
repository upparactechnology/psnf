@echo off
echo ==============================================
echo       Flutter Auto-Installer CLI Launcher
echo ==============================================
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0install_flutter.ps1"
pause
