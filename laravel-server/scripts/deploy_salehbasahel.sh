#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/salehbasahel"
STAGE_DIR="/root/salehbasahel-site"
DB_NAME="salehbasahel"
DB_USER="saleh_laravel"
DB_SECRET_FILE="/root/salehbasahel-db.env"
INTEGRATION_SECRET_FILE="/root/salehbasahel-integrations.env"

install -m 0644 "$STAGE_DIR/Lead.php" "$APP_DIR/app/Models/Lead.php"
install -m 0644 "$STAGE_DIR/LeadCommunication.php" "$APP_DIR/app/Models/LeadCommunication.php"
install -m 0644 "$STAGE_DIR/SiteContentOverride.php" "$APP_DIR/app/Models/SiteContentOverride.php"
install -m 0644 "$STAGE_DIR/SiteImageOverride.php" "$APP_DIR/app/Models/SiteImageOverride.php"
install -m 0644 "$STAGE_DIR/LeadController.php" "$APP_DIR/app/Http/Controllers/LeadController.php"
install -m 0644 "$STAGE_DIR/CalendlyWebhookController.php" "$APP_DIR/app/Http/Controllers/CalendlyWebhookController.php"
install -d "$APP_DIR/app/Http/Controllers/Admin"
install -m 0644 "$STAGE_DIR/Admin/AuthController.php" "$APP_DIR/app/Http/Controllers/Admin/AuthController.php"
install -m 0644 "$STAGE_DIR/Admin/ContentController.php" "$APP_DIR/app/Http/Controllers/Admin/ContentController.php"
install -m 0644 "$STAGE_DIR/Admin/DashboardController.php" "$APP_DIR/app/Http/Controllers/Admin/DashboardController.php"
install -m 0644 "$STAGE_DIR/Admin/LeadController.php" "$APP_DIR/app/Http/Controllers/Admin/LeadController.php"
install -m 0644 "$STAGE_DIR/Admin/ProfileController.php" "$APP_DIR/app/Http/Controllers/Admin/ProfileController.php"
install -d "$APP_DIR/app/Http/Middleware"
install -m 0644 "$STAGE_DIR/AdminAuthenticated.php" "$APP_DIR/app/Http/Middleware/AdminAuthenticated.php"
install -d "$APP_DIR/app/Services"
install -m 0644 "$STAGE_DIR/GoogleSheetLeadSync.php" "$APP_DIR/app/Services/GoogleSheetLeadSync.php"
install -m 0644 "$STAGE_DIR/OpenClawLeadImporter.php" "$APP_DIR/app/Services/OpenClawLeadImporter.php"
install -m 0644 "$STAGE_DIR/LeadScoringService.php" "$APP_DIR/app/Services/LeadScoringService.php"
install -m 0644 "$STAGE_DIR/OpenClawMessenger.php" "$APP_DIR/app/Services/OpenClawMessenger.php"
install -m 0644 "$STAGE_DIR/LeadEmailMessenger.php" "$APP_DIR/app/Services/LeadEmailMessenger.php"
install -m 0644 "$STAGE_DIR/LeadWelcomeService.php" "$APP_DIR/app/Services/LeadWelcomeService.php"
install -m 0644 "$STAGE_DIR/LeadAutomationService.php" "$APP_DIR/app/Services/LeadAutomationService.php"
install -m 0644 "$STAGE_DIR/LeadCommunicationService.php" "$APP_DIR/app/Services/LeadCommunicationService.php"
install -m 0644 "$STAGE_DIR/LeadCommunicationBackfillService.php" "$APP_DIR/app/Services/LeadCommunicationBackfillService.php"
install -o root -g root -m 0755 "$STAGE_DIR/openclaw_message_send.sh" /usr/local/bin/saleh-openclaw-send
printf 'www-data ALL=(openclaw) NOPASSWD: /usr/local/bin/saleh-openclaw-send\n' > /etc/sudoers.d/saleh-openclaw-send
chmod 0440 /etc/sudoers.d/saleh-openclaw-send
visudo -cf /etc/sudoers.d/saleh-openclaw-send
if [[ ! -f /home/openclaw-sender/.openclaw/identity/device-auth.json && -f "$STAGE_DIR/openclaw/configure_openclaw_sender.py" ]]; then
    install -o root -g root -m 0700 "$STAGE_DIR/openclaw/configure_openclaw_sender.py" /usr/local/sbin/saleh-configure-openclaw-sender
    /usr/local/sbin/saleh-configure-openclaw-sender
    uid="$(id -u openclaw)"
    sudo -u openclaw env XDG_RUNTIME_DIR="/run/user/$uid" systemctl --user restart openclaw-gateway.service
fi
install -d "$APP_DIR/app/Support"
install -m 0644 "$STAGE_DIR/SiteContent.php" "$APP_DIR/app/Support/SiteContent.php"
install -d "$APP_DIR/bootstrap"
install -m 0644 "$STAGE_DIR/bootstrap/app.php" "$APP_DIR/bootstrap/app.php"
install -d "$APP_DIR/config"
install -m 0644 "$STAGE_DIR/config/services.php" "$APP_DIR/config/services.php"
install -m 0644 "$STAGE_DIR/config/admin.php" "$APP_DIR/config/admin.php"
install -m 0644 "$STAGE_DIR/2026_05_31_000000_create_leads_table.php" "$APP_DIR/database/migrations/2026_05_31_000000_create_leads_table.php"
install -m 0644 "$STAGE_DIR/2026_06_03_000001_create_site_content_overrides_table.php" "$APP_DIR/database/migrations/2026_06_03_000001_create_site_content_overrides_table.php"
install -m 0644 "$STAGE_DIR/2026_06_03_000002_create_site_image_overrides_table.php" "$APP_DIR/database/migrations/2026_06_03_000002_create_site_image_overrides_table.php"
install -m 0644 "$STAGE_DIR/2026_06_04_000003_extend_leads_for_unified_management.php" "$APP_DIR/database/migrations/2026_06_04_000003_extend_leads_for_unified_management.php"
install -m 0644 "$STAGE_DIR/2026_06_04_000004_add_indexes_to_leads_management_fields.php" "$APP_DIR/database/migrations/2026_06_04_000004_add_indexes_to_leads_management_fields.php"
install -m 0644 "$STAGE_DIR/2026_06_05_000005_extend_leads_for_crm_automation.php" "$APP_DIR/database/migrations/2026_06_05_000005_extend_leads_for_crm_automation.php"
install -m 0644 "$STAGE_DIR/2026_06_05_000006_create_lead_import_tombstones_table.php" "$APP_DIR/database/migrations/2026_06_05_000006_create_lead_import_tombstones_table.php"
install -m 0644 "$STAGE_DIR/2026_06_05_000007_add_lead_message_automation_tracking.php" "$APP_DIR/database/migrations/2026_06_05_000007_add_lead_message_automation_tracking.php"
install -m 0644 "$STAGE_DIR/2026_06_06_000008_create_lead_communications_table.php" "$APP_DIR/database/migrations/2026_06_06_000008_create_lead_communications_table.php"
install -m 0644 "$STAGE_DIR/2026_06_06_000009_add_qualifier_steps_to_leads_table.php" "$APP_DIR/database/migrations/2026_06_06_000009_add_qualifier_steps_to_leads_table.php"
install -m 0644 "$STAGE_DIR/web.php" "$APP_DIR/routes/web.php"
install -m 0644 "$STAGE_DIR/console.php" "$APP_DIR/routes/console.php"
rm -f "$APP_DIR/resources/views/landing.blade.php"
install -m 0644 "$STAGE_DIR/saleh-basahel-landing-page.blade.php" "$APP_DIR/resources/views/saleh-basahel-landing-page.blade.php"
install -d "$APP_DIR/resources/views/components"
install -m 0644 "$STAGE_DIR/components/brand-logo.blade.php" "$APP_DIR/resources/views/components/brand-logo.blade.php"
install -m 0644 "$STAGE_DIR/components/header.blade.php" "$APP_DIR/resources/views/components/header.blade.php"
install -d "$APP_DIR/resources/views/components/admin" "$APP_DIR/resources/views/admin"
install -m 0644 "$STAGE_DIR/components/admin/layout.blade.php" "$APP_DIR/resources/views/components/admin/layout.blade.php"
install -m 0644 "$STAGE_DIR/Admin/content.blade.php" "$APP_DIR/resources/views/admin/content.blade.php"
install -m 0644 "$STAGE_DIR/Admin/dashboard.blade.php" "$APP_DIR/resources/views/admin/dashboard.blade.php"
install -m 0644 "$STAGE_DIR/Admin/layout.blade.php" "$APP_DIR/resources/views/admin/layout.blade.php"
install -m 0644 "$STAGE_DIR/Admin/leads.blade.php" "$APP_DIR/resources/views/admin/leads.blade.php"
install -m 0644 "$STAGE_DIR/Admin/lead-show.blade.php" "$APP_DIR/resources/views/admin/lead-show.blade.php"
install -m 0644 "$STAGE_DIR/Admin/login.blade.php" "$APP_DIR/resources/views/admin/login.blade.php"
install -m 0644 "$STAGE_DIR/Admin/profile.blade.php" "$APP_DIR/resources/views/admin/profile.blade.php"
install -d "$APP_DIR/lang/en" "$APP_DIR/lang/ar"
install -m 0644 "$STAGE_DIR/lang/en/site.php" "$APP_DIR/lang/en/site.php"
install -m 0644 "$STAGE_DIR/lang/ar/site.php" "$APP_DIR/lang/ar/site.php"
install -m 0644 "$STAGE_DIR/public/favicon.svg" "$APP_DIR/public/favicon.svg"
install -m 0644 "$STAGE_DIR/public/favcon.png" "$APP_DIR/public/favcon.png"
install -d "$APP_DIR/public/images"
install -m 0644 "$STAGE_DIR/public/images/hero-overview.svg" "$APP_DIR/public/images/hero-overview.svg"
install -m 0644 "$STAGE_DIR/public/images/hero-aside-image.png" "$APP_DIR/public/images/hero-aside-image.png"
install -m 0644 "$STAGE_DIR/public/images/hero-aside-image-768.webp" "$APP_DIR/public/images/hero-aside-image-768.webp"
install -m 0644 "$STAGE_DIR/public/images/hero-aside-image-1280.webp" "$APP_DIR/public/images/hero-aside-image-1280.webp"
install -m 0644 "$STAGE_DIR/public/images/hero-aside-image-1731.webp" "$APP_DIR/public/images/hero-aside-image-1731.webp"
install -m 0644 "$STAGE_DIR/public/images/hero-aside-image-768.jpg" "$APP_DIR/public/images/hero-aside-image-768.jpg"
install -m 0644 "$STAGE_DIR/public/images/hero-aside-image-1280.jpg" "$APP_DIR/public/images/hero-aside-image-1280.jpg"
install -m 0644 "$STAGE_DIR/public/images/hero-aside-image-1731.jpg" "$APP_DIR/public/images/hero-aside-image-1731.jpg"
install -m 0644 "$STAGE_DIR/public/images/wellnesslifestyle.png" "$APP_DIR/public/images/wellnesslifestyle.png"
install -m 0644 "$STAGE_DIR/public/images/profile.jpg" "$APP_DIR/public/images/profile.jpg"
install -m 0644 "$STAGE_DIR/public/images/video-overview-poster.svg" "$APP_DIR/public/images/video-overview-poster.svg"
install -d "$APP_DIR/public/videos"

if [[ -f "$DB_SECRET_FILE" ]]; then
    # shellcheck disable=SC1090
    source "$DB_SECRET_FILE"
else
    DB_PASS="$(openssl rand -hex 24)"
    umask 077
    printf 'DB_PASS=%q\n' "$DB_PASS" > "$DB_SECRET_FILE"
fi

if [[ -f "$INTEGRATION_SECRET_FILE" ]]; then
    # shellcheck disable=SC1090
    source "$INTEGRATION_SECRET_FILE"
fi

LEAD_WEBHOOK_TOKEN="${LEAD_WEBHOOK_TOKEN:-$(openssl rand -hex 32)}"
CALENDLY_WEBHOOK_TOKEN="${CALENDLY_WEBHOOK_TOKEN:-$(openssl rand -hex 32)}"
umask 077
printf 'LEAD_WEBHOOK_TOKEN=%q\nCALENDLY_WEBHOOK_TOKEN=%q\n' "$LEAD_WEBHOOK_TOKEN" "$CALENDLY_WEBHOOK_TOKEN" > "$INTEGRATION_SECRET_FILE"

mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"

python3 - "$APP_DIR/.env" "$DB_NAME" "$DB_USER" "$DB_PASS" "$LEAD_WEBHOOK_TOKEN" "$CALENDLY_WEBHOOK_TOKEN" <<'PY'
import sys
from pathlib import Path

path = Path(sys.argv[1])
db_name, db_user, db_pass, lead_token, calendly_token = sys.argv[2], sys.argv[3], sys.argv[4], sys.argv[5], sys.argv[6]
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
    "OPENCLAW_MESSAGING_ENABLED": "true",
    "OPENCLAW_BINARY": "/home/openclaw/.local/bin/openclaw",
    "OPENCLAW_SENDER": "/usr/local/bin/saleh-openclaw-send",
    "OPENCLAW_USER": "openclaw",
    "OPENCLAW_HOME": "/home/openclaw",
    "OPENCLAW_PATH": "/home/openclaw/.local/bin:/usr/local/bin:/usr/bin:/bin",
    "ADMIN_WHATSAPP_NUMBER": "+971555574958",
    "CALENDLY_URL": "https://calendly.com/salehbasahel/saleh-basahel",
    "LEAD_WEBHOOK_TOKEN": lead_token,
    "CALENDLY_WEBHOOK_TOKEN": calendly_token,
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
php artisan leads:backfill-communications
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data "$APP_DIR"
chmod -R ug+rwX "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

printf '* * * * * root cd %s && php artisan schedule:run >> /var/log/salehbasahel-scheduler.log 2>&1\n' "$APP_DIR" > /etc/cron.d/salehbasahel-scheduler
chmod 0644 /etc/cron.d/salehbasahel-scheduler
systemctl restart cron

if [[ -f "$STAGE_DIR/openclaw/DXN_OPENCLAW_WORKFLOW.md" && -f "$STAGE_DIR/openclaw/save_lead.py" ]]; then
    install -d -o openclaw -g openclaw /home/openclaw/.openclaw/workspace/scripts
    install -o openclaw -g openclaw -m 0644 "$STAGE_DIR/openclaw/DXN_OPENCLAW_WORKFLOW.md" /home/openclaw/.openclaw/workspace/DXN_OPENCLAW_WORKFLOW.md
    install -o openclaw -g openclaw -m 0644 "$STAGE_DIR/openclaw/AGENTS_APPEND_DXN.md" /home/openclaw/.openclaw/workspace/AGENTS_APPEND_DXN.md
    install -o openclaw -g openclaw -m 0755 "$STAGE_DIR/openclaw/save_lead.py" /home/openclaw/.openclaw/workspace/scripts/save_lead.py
    install -o openclaw -g openclaw -m 0755 "$STAGE_DIR/openclaw/update_agents_block.py" /home/openclaw/.openclaw/workspace/scripts/update_agents_block.py
    printf '%s\n' "$LEAD_WEBHOOK_TOKEN" > /home/openclaw/.openclaw/workspace/.lead_webhook_token
    chown openclaw:openclaw /home/openclaw/.openclaw/workspace/.lead_webhook_token
    chmod 0600 /home/openclaw/.openclaw/workspace/.lead_webhook_token
    sudo -u openclaw env HOME=/home/openclaw /home/openclaw/.openclaw/workspace/scripts/update_agents_block.py

    if [[ -f /home/openclaw/.openclaw/workspace/data/leads.jsonl ]]; then
        install -d -o www-data -g www-data "$APP_DIR/storage/app/openclaw"
        install -o www-data -g www-data -m 0640 /home/openclaw/.openclaw/workspace/data/leads.jsonl "$APP_DIR/storage/app/openclaw/leads.jsonl"
    fi
fi

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
