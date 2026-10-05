# Changelog — Super Admin

All notable changes made during the enterprise implementation pass on the HRMS project (this app is the platform/tenant-provisioning side). Migration files are listed but **not applied**.

## Phase 10 — Super Admin review
- `CI_ENVIRONMENT` flipped to `production`; `Config/Cookie.php` secure-flag now auto-follows `app.forceGlobalSecureRequests`; SMTP env placeholders added.
- New `subscriptions:expiry-sync` command: flips genuinely-expired subscriptions, emails renewal reminders (new `last_reminder_sent_at` tracking column, new `emails/subscription_expiring.php` template).
- Fixed `AuditService::log()` fataling when invoked from a CLI command (`getUserAgent()` doesn't exist on `CLIRequest`).

## Phase 11 — License System
- New `licenses` table + `LicenseModel` + `LicenseService` (generate/renew/revoke, HMAC-SHA256 signed, shared secret with HRMS via `license.signingSecret`).
- New `grace_days` field on `plans` (Super-Admin-configurable grace period).
- License generation wired into `CompanyProvisioningService::runProvisioning()`.
- Company view page now shows license status + renew/revoke actions (`CompaniesController::renewLicense()`/`revokeLicense()`).

## Phase 19 — Production Deployment
- New `.env.production.example` and `DEPLOYMENT_GUIDE.md` (pointing to the combined guide in the HRMS repo, which covers both apps together).

---
