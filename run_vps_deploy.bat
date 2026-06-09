@echo off
cd /d "E:\4- Web Development Project\xampp\htdocs\Saleh"
powershell -NoProfile -ExecutionPolicy Bypass -File "E:\4- Web Development Project\xampp\htdocs\Saleh\deploy_salehbasahel_to_vps.ps1"
if errorlevel 1 goto fail
pause
exit /b 0
:fail
echo.
echo Deploy failed. Please copy the error above and send it to Codex.
pause
exit /b 1
