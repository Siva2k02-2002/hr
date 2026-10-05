# Deployment Guide — Super Admin

This app is deployed as a pair with HRMS — the full combined guide (topology, both apps' `.env` templates, Apache/Nginx/Plesk/IIS configs, cron, backups, log rotation, queue-worker notes, and the go-live checklist) lives at `../HRMS/DEPLOYMENT_GUIDE.md` in this project's sibling repository, since most of it (topology, shared secrets, backup/cron patterns) applies to both apps together and is easier to keep correct as one document than as two copies that can drift out of sync.

This app's own production `.env` template is `.env.production.example` in this directory.

Quick reference — this app's own scheduled task:

| Command | Suggested schedule | Purpose |
|---|---|---|
| `php spark subscriptions:expiry-sync` | Daily | Marks expired subscriptions, emails renewal reminders |

See the combined guide for everything else (webserver configs, backups, TLS, go-live checklist).

## Document root

This app has no `public/` folder: point the vhost `DocumentRoot` at the project root. The root `.htaccess` is what keeps `.env`, `app/`, `vendor/` and `writable/` from being served, so the vhost needs `AllowOverride All` and `mod_rewrite`. On Nginx, add equivalent `location` denies (only `index.php`, `/assets/`, `favicon.ico` and `robots.txt` may be served as files).

## Clean URLs

`Config\App::$indexPage` is empty and the root `.htaccess` routes every non-file request to `index.php`, so URLs are `https://host/login`, never `/index.php/login`. In production set `app.baseURL = https://your-host/` and `app.forceGlobalSecureRequests = true` (all generated URLs then use HTTPS). If TLS terminates at a proxy, it must send `X-Forwarded-Proto: https` and the Apache vhost needs `AllowOverride All` (and `DirectoryIndex index.php`), otherwise `/` returns 403.
