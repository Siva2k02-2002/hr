# Changelog — HRMS

All notable changes made during the enterprise implementation pass. Grouped by phase. Migration files are listed but **not applied** — see the consolidated list in `FINAL_PRODUCTION_REPORT.md` (Phase 20) for the single run order.

## Phase 1 — P0 Launch Blockers
- Fixed password-reset token disclosure: `AuthController::forgotPassword()` now emails the reset link instead of returning it in the response. Added `app/Views/emails/password_reset.php`.
- Fixed overnight-shift attendance regularization date bug in `AttendanceRegularizationService::approve()`.
- Fixed employee form silently blocking submission on hidden-tab required fields (`employees/form.php`).
- `CI_ENVIRONMENT` flipped to `production`; `app.forceGlobalSecureRequests` / cookie-secure wiring documented.

## Phase 2 — Security Hardening
- Added `PasswordPolicyService` (strength + reuse via new `password_history` table), wired into change/reset/admin-set-password flows.
- Fixed admin-issued reset link disclosure (`UserService::sendResetLink()` now emails instead of returning the link).
- `Config/Cookie.php`: `secure` now auto-follows `app.forceGlobalSecureRequests`.
- Fixed `Content-Disposition` header injection risk in `EmployeeDocumentsController::download()`.
- Added `php spark tenants:migrate` command.
- Verified RBAC/IDOR/upload security across the app — no further gaps found.

## Phase 3 — Employee Module
- Added employee restore/archive (`EmployeesController::archived()`/`restore()`).
- Added duplicate-mobile/Aadhaar/PAN validation (`EmployeeService::assertUnique()`).
- Added bank account IFSC+account-number duplicate check.

## Phase 4 — Department / Designation / Branch
- Fixed missing employee-dependency guard on designation delete (`DesignationService::delete()`).
- Added duplicate-designation-name check.
- Added restore/archived views for Designations, Departments, Branches.

## Phase 5 — Attendance
- Wired up previously-dead `late_mark_minutes` and `timezone` settings.
- Added duplicate-holiday guard (`AttendanceHolidayService`).

## Phase 6 — Leave
- Fixed leave encashment balance not being re-checked at approval time (`LeaveBalanceService::debitForEncashment()`).

## Phase 7 — Payroll
- Fixed duplicate payroll generation race condition (row-locked `payroll_months`).
- Fixed PT/TDS being computed on non-taxable gross including reimbursements.
- Fixed salary-assignment date fallback picking a future-dated assignment.

## Phase 8 — Reports
- Added pagination to Attendance/Leave/Payroll report screens (exports remain unpaginated).

## Phase 9 — Dashboard
- Replaced the dashboard stub with real, permission-gated widgets (`DashboardService`).

## Phase 10 — Super Admin
- Fixed `CI_ENVIRONMENT`/cookie/SMTP config parity with HRMS.
- Added company employee-limit enforcement in HRMS (`EmployeeService::assertUnderEmployeeLimit()`), reading Super Admin's `employee_limit` via the read-only `platform` DB connection.
- Added `subscriptions:expiry-sync` command (Super Admin) + renewal-reminder email.
- Fixed `AuditService` fataling when called from a CLI context.

## Phase 11 — License System
- New `licenses` table (Super Admin) + `LicenseService` (generate/renew/revoke, HMAC-SHA256 signed).
- New `LicenseVerificationService` (HRMS), integrated into `TenantResolver`: signature, revocation, domain, expiry, grace-period checks.
- Offline verification cache with tamper-safe fallback.
- Grace-period banner in HRMS layouts; license audit events logged.
- New commands: `licenses:refresh-cache`, `licenses:cleanup-cache`.

## Phase 12 — Settings Module
- New company branding fields (`logo_path`, `favicon_path`, `banner_path`, `theme`, `accent_color`, `font_family`, `default_language`, `week_start_day`) via migration.
- New `CompanyBrandingService` + `CompanyBrandingController` (upload + serve, outside webroot, same MIME-sniff pattern as employee documents/photos).
- Logo/favicon/accent color now render in the sidebar, both auth/main layout heads, and the login page.
- New SMTP diagnostics page (`SmtpSettingsController`) — shows current `.env`-sourced config (masked) and sends a real test email. SMTP credentials intentionally stay in `.env`, not the DB (no encryption-at-rest layer exists for DB-stored secrets).
- Verified: financial-year-start and working-day patterns already existed (`leave_settings`, `attendance_weekly_offs`) — not duplicated.

## Phase 13 — Notification System
- New `notifications` table + `NotificationModel` + `NotificationService` (in-app + email channels; SMS/WhatsApp/push implemented as logged placeholders per spec).
- Wired into: leave apply/approve/reject (`LeaveApplicationService`, `LeaveApprovalService`), attendance regularization approval, payroll generated (notifies everyone holding `payroll.approve`), payslip available (fires on payroll `pay()`, in-app + email).
- New `notifications:daily-reminders` command: birthday/work-anniversary reminders to HR, across every tenant.
- Real notification bell + dropdown wired into the topbar (was a permanent "all caught up" placeholder); new `/notifications` inbox page.

## Phase 14 — Database Optimization
- Reviewed all 79 migrations (via background research pass). Added missing indexes: `login_logs.user_id`, `audit_logs(module, record_id)` composite.
- Verified: all other FK-shaped `_id` columns across employees/attendance/leave/payroll tables already have matching indexes/FKs/unique keys — no further gaps found.
- Noted (not fixed): `attendance_logs` has no `deleted_at` while its parent `attendance` does — soft-deleting an attendance row doesn't cascade, since CI4 soft-deletes don't fire FK actions. Flagged, not changed (low severity, would need a behavioral decision, not just a migration).

## Phase 15 — Performance Optimization
- New `LookupCacheService` (6h TTL, per-tenant-prefixed keys) for departments/branches/designations/company settings/leave types/active holidays, with invalidation wired into every service that writes to those tables.
- New `asset_url()` helper — appends a `filemtime()`-based `?v=` to every local CSS/JS reference in both layouts (CDN scripts untouched).
- Rewrote CSV export in all 4 export services (Attendance/Leave/Payroll/Employee) to stream via `fputcsv()` into `php://temp` instead of building a full PhpSpreadsheet object graph just to flatten it back to CSV — meaningfully lower memory use on large report exports.
- Report pagination was already added in Phase 8.
- Deliberately not attempted: batching payroll generation's per-employee bonus/incentive/reimbursement/arrears queries (a real N+1, ~4 queries × N employees) — refactoring financial calculation code without the ability to test against a live database was judged too risky to rush.

## Phase 16 — UI/UX Improvements
- New branded error pages replacing CI4's stock unbranded ones: 400, 401, 403, 404, 419, 500 (`app/Views/errors/html/`), matching the existing `errors/tenant_403.php` visual style. CI4 auto-selects `error_{statusCode}.php` by convention, so these apply automatically — no controller wiring needed.
- New site-wide maintenance mode: `MaintenanceModeFilter` (global, runs ahead of tenant resolution so it works even if the platform DB itself is the thing under maintenance), toggled by `app.maintenanceMode` in `.env`, with a token-based bypass for ops/QA. New branded `errors/html/maintenance.php`.
- Verified via the existing CSS/JS (not rebuilt): skeleton loaders, sticky table columns/headers, toast notifications, and confirm-dialog infrastructure already exist app-wide (`components.css`, `table.css`, `app.js`) — this was already a well-built UI layer, not a gap.
- Not attempted: a full visual responsive/mobile/tablet review across every view — I have no way to render a browser to verify layout behavior, and guessing at CSS changes without seeing the result risks making things worse, not better.

## Phase 17 — Audit Log System
- New `audit_logs.employee_id` column: lets an event be tagged with the employee it's *about*, independent of which table `record_id` actually points to (a leave-approval event's `record_id` is the leave_application's id, not the employee's). Wired into `EmployeeService`, `LeaveApplicationService`, `LeaveApprovalService`, `AttendanceRegularizationService`.
- Fixed `EmployeesController::auditLogsFor()` (the employee profile's "Activity" tab) to actually use it — previously only showed `module='employees'` rows, so leave/attendance events about that same employee never appeared on their own timeline.
- `AuditService::log()` now CLI-safe (same fix as Super Admin's, Phase 10) — `getUserAgent()` doesn't exist on `CLIRequest`, so any future scheduled command touching an audited service would have fatally errored.
- Verified: before/after values, user_id, module, action, IP, user-agent, and timestamp were already captured (Phase 2). `company_id` deliberately not added — `audit_logs` lives inside each tenant's own database, so the database itself is the company scope.

## Phase 18 — Automated Testing
- Full `php -l` sweep across every `.php` file in both repos' `app/` trees — 0 syntax errors.
- New PHPUnit tests (16 tests / 24 assertions, all executed and passing, no DB required): `tests/unit/LeaveFinancialYearTest.php`, `tests/unit/EmployeeHelperTest.php`, `tests/unit/AttendanceExportServiceTest.php`.
- New `TEST_REPORT.md` — clearly separates what was actually executed (syntax validation, the 3 new test files) from a documented functional/security/edge-case test plan that needs an applied schema and real data to run.

## Phase 19 — Production Deployment
- New `.env.production.example` (both repos) — every value templated, no secrets left in place.
- New `DEPLOYMENT_GUIDE.md` (combined, both apps): topology, Apache/Nginx/Plesk/IIS configs, writable-permissions, cron table for every command built across this project, backup strategy (per-tenant `mysqldump`, since each company has its own physical database), log rotation, cache cleanup, and a go-live checklist.
- Documented, not built: a real queue worker. This codebase sends every email/notification synchronously inline — flagged clearly as a genuine future architecture change rather than a config toggle, since claiming a working queue config for code that doesn't queue anything would be dishonest.

## Phase 20 — Final Production Verification
- New `FINAL_PRODUCTION_REPORT.md`: executive summary, all fixes by module, the full 10-migration consolidated list with run order, DB/queue/cron commands, SMTP/security/performance/UI/production checklists, and a final scorecard (~81/100 overall — not claimed as 100, with each score's gap explicitly stated).
- Final full `php -l` sweep across both repos re-run clean immediately before writing the report.

---
