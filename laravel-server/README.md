# Laravel Server Files

This folder contains the Laravel website files used for `salehbasahel.com`.

The live Laravel app on the Contabo VPS is installed at:

```text
/var/www/salehbasahel
```

These files map to the VPS app like this:

```text
app/Models/Lead.php
app/Http/Controllers/LeadController.php
app/Services/GoogleSheetLeadSync.php
database/migrations/2026_05_31_000000_create_leads_table.php
routes/web.php
resources/views/saleh-basahel-landing-page.blade.php
server/salehbasahel.nginx
scripts/deploy_salehbasahel.sh
scripts/cleanup_test_lead.sh
```

The deployed website is:

```text
https://salehbasahel.com
```

Do not put `.env`, database passwords, SSL private keys, or server secrets in this folder.
