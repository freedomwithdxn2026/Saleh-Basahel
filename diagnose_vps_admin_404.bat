@echo off
cd /d "E:\4- Web Development Project\xampp\htdocs\Saleh"
echo Checking VPS admin 404 cause...
echo You may be asked for the VPS root password.
echo.
ssh root@161.97.114.28 "set -e; echo '--- nginx site config ---'; nginx -T 2>/dev/null | grep -A35 -B5 'server_name salehbasahel.com' || true; echo; echo '--- active app path ---'; ls -ld /var/www/salehbasahel /var/www/salehbasahel/public || true; echo; echo '--- route file admin lines ---'; grep -n 'admin/login\|Route::prefix.*admin\|AdminDashboard' /var/www/salehbasahel/routes/web.php || true; echo; echo '--- cached routes grep ---'; grep -R 'admin/login' /var/www/salehbasahel/bootstrap/cache 2>/dev/null || true; echo; echo '--- artisan route list admin ---'; cd /var/www/salehbasahel && php artisan route:clear && php artisan optimize:clear && php artisan route:list | grep admin || true; echo; echo '--- laravel route file checksum ---'; sha256sum /var/www/salehbasahel/routes/web.php; echo; echo '--- php fpm/nginx reload ---'; nginx -t && systemctl reload nginx" > vps_admin_404_diagnosis.log 2>&1
echo.
echo Diagnosis saved to:
echo E:\4- Web Development Project\xampp\htdocs\Saleh\vps_admin_404_diagnosis.log
echo.
type "E:\4- Web Development Project\xampp\htdocs\Saleh\vps_admin_404_diagnosis.log"
pause
