#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/salehbasahel"
STAGE_DIR="/root/salehbasahel-site"
DB_NAME="salehbasahel"
DB_USER="saleh_laravel"
DB_SECRET_FILE="/root/salehbasahel-db.env"

install -m 0644 "$STAGE_DIR/Lead.php" "$APP_DIR/app/Models/Lead.php"
install -m 0644 "$STAGE_DIR/LeadController.php" "$APP_DIR/app/Http/Controllers/LeadController.php"
install -m 0644 "$STAGE_DIR/2026_05_31_000000_create_leads_table.php" "$APP_DIR/database/migrations/2026_05_31_000000_create_leads_table.php"
install -m 0644 "$STAGE_DIR/web.php" "$APP_DIR/routes/web.php"
install -m 0644 "$STAGE_DIR/landing.blade.php" "$APP_DIR/resources/views/landing.blade.php"

if [[ -f "$DB_SECRET_FILE" ]]; then
    # shellcheck disable=SC1090
    source "$DB_SECRET_FILE"
else
    DB_PASS="$(openssl rand -hex 24)"
    umask 077
    printf 'DB_PASS=%q\n' "$DB_PASS" > "$DB_SECRET_FILE"
fi

mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"

python3 - "$APP_DIR/.env" "$DB_NAME" "$DB_USER" "$DB_PASS" <<'PY'
import sys
from pathlib import Path

path = Path(sys.argv[1])
db_name, db_user, db_pass = sys.argv[2], sys.argv[3], sys.argv[4]
updates = {
    "APP_NAME": "Saleh Basahel",
    "APP_ENV": "production",
    "APP_DEBUG": "false",
    "APP_URL": "https://salehbasahel.com",
    "DB_CONNECTION": "mysql",
    "DB_HOST": "127.0.0.1",
    "DB_PORT": "3306",
    "DB_DATABASE": db_name,
    "DB_USERNAME": db_user,
    "DB_PASSWORD": db_pass,
    "SESSION_DRIVER": "database",
    "CACHE_STORE": "database",
    "QUEUE_CONNECTION": "database",
}

lines = path.read_text().splitlines()
seen = set()
out = []

for line in lines:
    key = line.split("=", 1)[0] if "=" in line else None
    if key in updates:
        value = updates[key]
        if any(ch in value for ch in " #\"'"):
            value = '"' + value.replace('"', '\\"') + '"'
        out.append(f"{key}={value}")
        seen.add(key)
    else:
        out.append(line)

for key, value in updates.items():
    if key not in seen:
        if any(ch in value for ch in " #\"'"):
            value = '"' + value.replace('"', '\\"') + '"'
        out.append(f"{key}={value}")

path.write_text("\n".join(out) + "\n")
PY

cd "$APP_DIR"
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data "$APP_DIR"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

install -m 0644 "$STAGE_DIR/salehbasahel.nginx" /etc/nginx/sites-available/salehbasahel
ln -sfn /etc/nginx/sites-available/salehbasahel /etc/nginx/sites-enabled/salehbasahel
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

certbot --nginx \
    -d salehbasahel.com \
    -d www.salehbasahel.com \
    --non-interactive \
    --agree-tos \
    --redirect \
    --register-unsafely-without-email
nginx -t
systemctl reload nginx

ufw allow 'Nginx Full'
ufw --force reload
