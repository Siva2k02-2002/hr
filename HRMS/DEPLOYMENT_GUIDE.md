# Deployment Guide — HRMS + Super Admin

Covers both applications, since they're deployed as a pair: Super Admin provisions tenants and issues licenses; HRMS serves every tenant's own subdomain. Nothing in this guide has been executed — it's a reference for whoever performs the actual deployment.

## 1. Topology

```
                         ┌─────────────────────┐
  *.yourdomain.com  ───▶ │   HRMS (tenant app)  │──▶ per-tenant MySQL DB (hrms_<code>)
                         └─────────────────────┘         ▲
                                    │ tenant DB credentials      │ connection stored
                                    │ (encrypted, platform DB)    │ + verified read-only
                         ┌─────────────────────┐                │
  superadmin.yourdomain  │  Super Admin app     │────────────────┘
  .com               ───▶│                      │──▶ hrms_platform DB
                         └─────────────────────┘
```

Both apps share two secrets that **must** be byte-for-byte identical in each `.env` — `encryption.key` and `license.signingSecret`. Super Admin never calls the HRMS app over HTTP. Generate each once, copy into both files.

## 2. HRMS — `.env`

Copy `.env.production.example` → `.env` and fill in every `REPLACE-WITH-...` value. Key points:
- `app.baseURL` should be a wildcard-capable value if serving multiple tenant subdomains from one vhost — the actual per-request domain comes from the `Host` header (`TenantResolver`), not this setting.
- `database.platform.*` must point at a **read-only** MySQL account (`platform_reader` or similar) — HRMS never writes to the platform database.
- `app.forceGlobalSecureRequests = true` only after TLS is actually terminating in front of the app (see §4/§5) — enabling it before that will redirect-loop.

## 3. Super Admin — `.env`

Copy `.env.production.example` → `.env`. `database.default.*` here needs full read/write — tenant databases and their MySQL users are created manually (Plesk/phpMyAdmin); Super Admin only stores and read-only-verifies the connection, so it needs no `CREATE DATABASE`/`CREATE USER`/`GRANT` privileges.

## 4. Apache VirtualHost

Two separate vhosts (or vhost + subdomain if same box). Neither app uses a `public/` folder: point `DocumentRoot` at each app's project root. The root `.htaccess` serves only `index.php`, `assets/`, `favicon.ico` and `robots.txt` and returns 403 for everything else (`.env`, `app/`, `vendor/`, `writable/`), so it must be honoured (`AllowOverride All`, `mod_rewrite`). On Nginx/IIS add equivalent denies — see the notes below.

```apache
# HRMS — wildcard tenant subdomains
<VirtualHost *:443>
    ServerName hrms.yourdomain.com
    ServerAlias *.yourdomain.com
    DocumentRoot "/var/www/hrms"

    <Directory "/var/www/hrms">
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile      /etc/ssl/certs/wildcard.yourdomain.com.crt
    SSLCertificateKeyFile   /etc/ssl/private/wildcard.yourdomain.com.key
    SSLCertificateChainFile /etc/ssl/certs/wildcard-chain.crt

    ErrorLog  ${APACHE_LOG_DIR}/hrms-error.log
    CustomLog ${APACHE_LOG_DIR}/hrms-access.log combined
</VirtualHost>

<VirtualHost *:80>
    ServerName hrms.yourdomain.com
    ServerAlias *.yourdomain.com
    Redirect permanent / https://hrms.yourdomain.com/
</VirtualHost>

# Super Admin — single fixed hostname
<VirtualHost *:443>
    ServerName superadmin.yourdomain.com
    DocumentRoot "/var/www/superadmin"

    <Directory "/var/www/superadmin">
        AllowOverride All
        Require all granted
    </Directory>

    SSLEngine on
    SSLCertificateFile      /etc/ssl/certs/superadmin.yourdomain.com.crt
    SSLCertificateKeyFile   /etc/ssl/private/superadmin.yourdomain.com.key

    ErrorLog  ${APACHE_LOG_DIR}/superadmin-error.log
    CustomLog ${APACHE_LOG_DIR}/superadmin-access.log combined
</VirtualHost>
```

Requires `mod_rewrite` (for CI4's front-controller routing, via the root `.htaccess`) and `mod_ssl`.

## 5. Nginx (with PHP-FPM)

```nginx
server {
    listen 443 ssl http2;
    server_name hrms.yourdomain.com *.yourdomain.com;
    root /var/www/hrms;
    index index.php;

    ssl_certificate     /etc/ssl/certs/wildcard.yourdomain.com.crt;
    ssl_certificate_key /etc/ssl/private/wildcard.yourdomain.com.key;

    access_log /var/log/nginx/hrms-access.log;
    error_log  /var/log/nginx/hrms-error.log;

    # No public/ folder: only index.php, /assets/, favicon.ico and robots.txt may be
    # served as real files; everything else (.env, app/, vendor/, ...) goes to the
    # front controller. Keep this block above the \.php$ location.
    location ~ ^/(?!(index\.php|assets/|favicon\.ico$|robots\.txt$)) {
        try_files /index.php$is_args$args =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known) { deny all; }
}

server {
    listen 80;
    server_name hrms.yourdomain.com *.yourdomain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name superadmin.yourdomain.com;
    root /var/www/superadmin;
    index index.php;

    ssl_certificate     /etc/ssl/certs/superadmin.yourdomain.com.crt;
    ssl_certificate_key /etc/ssl/private/superadmin.yourdomain.com.key;

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## 6. Plesk

1. Create two domains/subdomains, each with the **document root set to the project root** (Plesk: Domains → Hosting Settings → Document root → the app folder itself).
2. PHP 8.1+ via Plesk's PHP handler (FPM application served by nginx recommended).
3. Set environment variables or rely on the `.env` file directly — Plesk doesn't require anything special for CI4 beyond the document-root change.
4. Enable "SSL/TLS support" and "Permanent SEO-safe 301 redirect from HTTP to HTTPS" per domain (Let's Encrypt via Plesk's built-in extension covers both, including a wildcard cert for HRMS's `*.yourdomain.com`).
5. Scheduled tasks (§9) go under Plesk's **Scheduled Tasks** per subscription, running `php /var/www/vhosts/.../spark <command>`.

## 7. Windows Server / IIS

CI4 runs on IIS via the FastCGI PHP handler. Notes specific to this stack:
- Site physical path → each app's `public\` folder.
- Install `URL Rewrite` module; CI4 needs a `web.config` in the project root translating requests to `index.php` (CI4 ships a default rewrite rule set for Apache via `.htaccess`; for IIS write an equivalent `<rewrite>` rule redirecting non-file/non-directory requests to `index.php`).
- PHP via `php-cgi.exe` through FastCGI, matching the PHP version this app was built against (8.1+).
- File permissions: the IIS app pool identity needs write access to `writable/` (cache, logs, session, uploads) — same as `chmod`/`chown` would grant on Linux.
- Scheduled tasks (§9) via **Windows Task Scheduler**, running `php.exe C:\inetpub\hrms\spark <command>` on the desired interval — this is exactly the environment this project was developed under (XAMPP on Windows), so the `spark` commands themselves need no changes.

## 8. Writable permissions

Both apps need write access (not execute) for the web server user on:
- `writable/cache/`, `writable/logs/`, `writable/session/`, `writable/uploads/`
- HRMS additionally: `writable/uploads/tenants/` (created per-tenant automatically by the upload services — the parent must be writable)

Linux: `chown -R www-data:www-data writable/ && find writable -type d -exec chmod 750 {} \;`
Windows/IIS: grant the app pool identity Modify on the `writable` folder.

## 9. Cron / Scheduled Tasks

None of these are scheduled automatically anywhere in this codebase — set them up explicitly at deployment. All run from each app's own root directory.

| App | Command | Suggested schedule | Purpose |
|---|---|---|---|
| Super Admin | `php spark subscriptions:expiry-sync` | Daily, e.g. 02:00 | Marks expired subscriptions, emails renewal reminders |
| HRMS | `php spark licenses:refresh-cache` | Every 4–6 hours | Keeps the offline license cache warm |
| HRMS | `php spark licenses:cleanup-cache` | Daily | Removes stale/orphaned cache entries |
| HRMS | `php spark notifications:daily-reminders` | Daily, e.g. 07:00 | Birthday/work-anniversary reminders, all tenants |
| HRMS | `php spark tenants:migrate` | On-demand, after deploying a schema change | Applies pending migrations to every already-provisioned tenant |

Linux crontab example (HRMS):
```cron
0 2 * * * cd /var/www/hrms && php spark licenses:cleanup-cache >> writable/logs/cron.log 2>&1
0 */4 * * * cd /var/www/hrms && php spark licenses:refresh-cache >> writable/logs/cron.log 2>&1
0 7 * * * cd /var/www/hrms && php spark notifications:daily-reminders >> writable/logs/cron.log 2>&1
```

## 10. Queue worker

**No real queue infrastructure exists in this codebase.** `NotificationService` and every email-sending call (`AuthController::forgotPassword()`, `EmailVerificationService`, `PayrollPayService`, etc.) sends synchronously, inline, during the web request. This is fine at moderate volume but means a slow SMTP provider adds directly to page load time, and a payroll `pay()` action emailing an entire company's payslips happens serially in one request.

If queueing becomes necessary: CodeIgniter 4 doesn't ship a queue system out of the box. The straightforward path with this codebase is a **database-backed job table** (a `queued_jobs` table + a `queue:work` spark command run continuously under a process supervisor like `supervisord` or as a Windows service) — `NotificationService::email()` would insert a job row instead of calling `service('email')->send()` directly, and the worker command would send it and mark it done. This is a real architecture change, not a config toggle, and wasn't built in this pass — flagging it here rather than shipping a fake "queue config" that doesn't correspond to working code.

## 11. Backups

Every tenant has its own physical MySQL database (`hrms_<code>`), plus the shared `hrms_platform` database. Back up per-database, not as one dump, so a single tenant can be restored independently.

```bash
# List every tenant DB name from the platform DB, then dump each one
mysql -N -e "SELECT db_name FROM hrms_platform.company_database_connections WHERE status='provisioned'" \
  | while read -r db; do
      mysqldump --single-transaction --routines --triggers "$db" | gzip > "/backups/$(date +%F)-$db.sql.gz"
    done

# Platform DB itself
mysqldump --single-transaction --routines --triggers hrms_platform | gzip > "/backups/$(date +%F)-hrms_platform.sql.gz"
```

Also back up `writable/uploads/` (per-tenant documents/photos/branding — not in the database) on both apps' file storage, and each `.env` file (encrypted at rest / restricted access, since these contain live credentials).

## 12. Log rotation

CI4 writes to `writable/logs/*.log` with no built-in rotation. Standard `logrotate` config (Linux):

```
/var/www/hrms/writable/logs/*.log /var/www/superadmin/writable/logs/*.log {
    daily
    rotate 14
    compress
    delaycompress
    missingok
    notifempty
    create 0640 www-data www-data
}
```

On Windows, use Task Scheduler with a small script that archives/deletes logs older than N days, or a log-rotation utility if your environment already has one standardized.

## 13. Cache cleanup

`LookupCacheService` (HRMS, Phase 15) uses CI4's file cache with a 6-hour TTL and explicit invalidation on writes — no manual cleanup needed, entries simply expire. If disk usage in `writable/cache/` ever becomes a concern, `php spark cache:clear` (CI4 built-in) clears it entirely; safe to run any time since everything it holds is a read-through cache of the database.

## 14. Go-live checklist

1. Both `.env` files filled in from the `.env.production.example` templates, secrets matching between the two apps where required.
2. TLS certificates installed; `app.forceGlobalSecureRequests = true` in both.
3. `writable/` permissions set on both apps.
4. Consolidated migrations applied (see `FINAL_PRODUCTION_REPORT.md`) — Super Admin first (subscriptions/plans/licenses tables), then `php spark tenants:migrate` from HRMS for every already-provisioned tenant.
5. SMTP configured and verified via HRMS's `/settings/smtp` test-email page.
6. Cron/Scheduled Tasks from §9 installed.
7. Backups (§11) and log rotation (§13) configured and tested with one real run each.
8. `CI_ENVIRONMENT = production` confirmed in both `.env` files (debug toolbar/error details off).
