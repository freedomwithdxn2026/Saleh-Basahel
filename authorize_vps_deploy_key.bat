@echo off
cd /d "E:\4- Web Development Project\xampp\htdocs\Saleh"
echo Authorizing Saleh VPS deploy key...
echo Type the VPS root password when asked.
ssh root@161.97.114.28 "umask 077; mkdir -p ~/.ssh; touch ~/.ssh/authorized_keys; grep -qxF 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIId7jq+fFIUHHMr1Fl3ATvQvNKsL7W/TnLMeNoEzZ8iG saleh-vps-deploy-key' ~/.ssh/authorized_keys || echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIId7jq+fFIUHHMr1Fl3ATvQvNKsL7W/TnLMeNoEzZ8iG saleh-vps-deploy-key' >> ~/.ssh/authorized_keys; chmod 700 ~/.ssh; chmod 600 ~/.ssh/authorized_keys"
if errorlevel 1 goto fail
echo.
echo Key authorized successfully.
pause
exit /b 0
:fail
echo.
echo Key authorization failed. Please copy the error above and send it to Codex.
pause
exit /b 1
