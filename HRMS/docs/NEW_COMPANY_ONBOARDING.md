# New Company Onboarding – Developer Handover

Written for: the developer who sets up the next company on the HRMS platform.

## 1. How the system fits together

| Part | What it is | Database |
|---|---|---|
| **Super Admin** (`superadmin/`) | Platform control panel: companies, domains, plans, subscriptions, licenses | Platform DB (live: `hrms_platform_master`) |
| **HRMS** (`HRMS/`) | The product tenants use. One codebase serves every company | One **separate database per company** |

Request flow: browser opens `acme.hrcloud.in` → `TenantResolver` reads the **Host header** → looks it up in the platform DB (`company_domains` → `companies` → `subscriptions` → `licenses` → `company_database_connections`) → connects to that company's own database.

Super Admin **never creates databases or imports SQL**. A developer does that by hand (phpMyAdmin/Plesk), then clicks **Connect & Verify**.

## 2. Files you need

| File | Purpose |
|---|---|
| `HRMS/hrms_tenant_install_template.sql` | Generic company database (68 tables, roles, permissions, default data). Edit 3 values at the top, import into an empty DB |
| `HRMS/hrms_platform.schema.sql` | Platform DB structure (only for setting up a brand-new platform, not per company) |

Tested: the template imports cleanly into an empty database and creates 68 tables, 4 roles, 85 permissions, one company-settings row and one admin user.

## 3. Onboarding a new company – step by step

### Step 1 – DNS / hosting (once per domain)
- Subdomain (`acme.hrcloud.in`): add a wildcard `*.hrcloud.in` A record to the server, or one A record per subdomain. Add an SSL certificate that covers it (wildcard recommended).
- The hosting (virtual host/document root) must send that hostname to the HRMS `public/` folder.
- Super Admin only generates `<subdomain>.<app.baseDomain>`. A company's own **custom main domain** needs an extra `company_domains` row plus DNS/SSL for that domain. Ask for approval before adding one by SQL.

### Step 2 – Create the company in Super Admin
1. Super Admin → Companies → **Add company**.
2. Fill: code (lowercase letters/numbers, 2–21 chars, not reserved), name, subdomain, plan, employee limit, admin name, admin email.
3. Set the subscription: start date, expiry date, status (`trial` or `active`).
4. Result: company with `provisioning_status = pending`, one `company_domains` row, one subscription. Note the **company ID**.

### Step 3 – Create the company database (hosting panel)
1. Create an empty database, e.g. `hrms_<code>`, charset `utf8mb4`, collation `utf8mb4_general_ci`.
2. Create a MySQL user for it with a strong password and grant it **only that database**.
3. Do not reuse a database already assigned to another company (Super Admin rejects that).

### Step 4 – Import the SQL
1. Copy `hrms_tenant_install_template.sql`. Do not edit the original.
2. In the copy, change only the block at the top:
   ```sql
   SET @company_name = 'Acme Pvt Ltd';
   SET @admin_name   = 'Acme Admin';
   SET @admin_email  = 'admin@acme.com';
   ```
3. phpMyAdmin → select the new empty database → **Import** → choose the copy. (For large files raise `upload_max_filesize`, or use the command line:
   `mysql -u USER -p DBNAME < file.sql`).
4. Check: the database now has 68 tables and `SELECT * FROM company_settings;` shows the company name.

### Step 5 – Connect & Verify (Super Admin)
1. Company → Database Connection → enter host, port, database name, DB username, DB password → **Test Connection**.
2. If the test passes, **Connect & Verify**. It runs read-only checks (connection, required tables, company settings, roles and permissions), encrypts and stores the credentials, and sets `provisioning_status = ready`.
3. If it fails, the company is marked `failed` with the reason. Fix the cause and repeat.

### Step 6 – License
- Super Admin → company → License → **Issue/Generate** (Renew later for extensions). The license is stored in the platform DB, not in the company DB.
- The license `domain` must equal the company's primary domain.

### Step 7 – Set the admin password
The template creates the admin with an unusable password. Either use "Forgot password" on the company login page (needs SMTP configured), or set it manually:
```
php -r "echo password_hash('NewStrongPassword', PASSWORD_DEFAULT);"
```
```sql
UPDATE users SET password_hash='<hash from above>' WHERE id=1;
```
Run that on the **company's** database. The user must change the password at first login.

### Step 8 – Verify the company works
1. Open `https://<subdomain>.<basedomain>/` – you should reach the login page, not an error.
2. Log in as the admin.
3. Check the dashboard, Employees, Settings load without errors.
4. Check Super Admin shows the company as `ready` with a valid subscription and license.

## 4. What each error message means

| Message | Meaning | Fix |
|---|---|---|
| Company not found (404) | No `company_domains` row for this host | Check domain spelling; add domain in Super Admin |
| Workspace still being set up (503) | `provisioning_status` not `ready` or connection not `provisioned` | Finish Step 5 |
| Subscription is not active (403) | No current subscription, or status is `suspended`/`cancelled` | Check `companies.current_subscription_id` and the subscription row |
| Subscription has expired (403) | `subscriptions.expires_at` is before today (expiry day itself is still valid) | Extend in Super Admin |
| License revoked / expired / not valid for this domain (403) | License row problem | Renew or reissue the license in Super Admin |
| Service temporarily unavailable (503) | Tenant DB unreachable or the stored DB password can't be decrypted | Check DB credentials; `encryption.key` must be the same in both apps |

## 5. Production configuration rules

- `encryption.key` must be **identical** in HRMS and Super Admin `.env`. Changing it makes every stored tenant DB password unreadable.
- License checking: HRMS reads license status, domain and expiry from the platform DB and no longer verifies a signature, so the signing secret does not have to match between apps.
- The platform connection in HRMS must use a **SELECT-only** MySQL user.
- Live HRMS reads the platform DB through the group name `platform` (`database.platform.*` in `.env`); the repository's newer code uses `master` (`database.master.*`). Keep the `.env` keys and `app/Config/Database.php` group name consistent with the code that is deployed.
- Never paste `.env` contents into chats or tickets; rotate any password that has been exposed.
- After uploading code: reload PHP-FPM (or clear OPcache). Check `writable/logs/` if something fails.

## 6. Backups and safety

- Before any import or change: export the platform DB and the company DB.
- Never delete or manually edit rows in `licenses`, `subscriptions` or `companies` in phpMyAdmin; use Super Admin actions so records stay consistent.
- Upload only files that belong to the change; do not copy the whole working tree over production.

## 7. Quick diagnosis SQL (read-only, run on the platform DB)

```sql
SELECT cd.domain, c.id, c.code, c.status, c.provisioning_status,
       c.current_subscription_id, s.status AS sub_status, s.expires_at,
       cdc.status AS conn_status
FROM company_domains cd
JOIN companies c ON c.id = cd.company_id
LEFT JOIN subscriptions s ON s.id = c.current_subscription_id
LEFT JOIN company_database_connections cdc ON cdc.company_id = c.id
WHERE cd.domain = 'acme.hrcloud.in';

SELECT id, domain, issued_at, expires_at, status
FROM licenses WHERE company_id = <ID> ORDER BY id DESC LIMIT 1;
```
