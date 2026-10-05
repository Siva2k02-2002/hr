<?php

namespace App\Services;

use App\Models\CompanyDatabaseConnectionModel;
use App\Models\CompanyDomainModel;
use App\Models\CompanyModel;
use App\Models\PlanModel;
use App\Models\ProvisioningLogModel;
use App\Models\SubscriptionModel;
use RuntimeException;
use Throwable;

/**
 * Company onboarding: validate -> create platform records -> (administrator
 * creates the tenant database + user and imports the complete HRMS SQL
 * manually in Plesk/phpMyAdmin) -> "Connect & Verify" the supplied
 * connection -> mark READY.
 *
 * This application NEVER creates databases or MySQL users, never issues
 * GRANT, never imports SQL, runs migrations, seeds RBAC, creates tenant
 * users, and never calls the tenant HRMS
 * application over HTTP. (Provisioning never writes company_settings; the
 * separate Company Settings screen — TenantSettingsService — updates it
 * after a company is ready.) Every check against the tenant database is a
 * read-only SELECT; the supplied credentials are stored (encrypted) in the
 * platform database only.
 *
 * A company's `status` (trial/active/...) is a SEPARATE concern from
 * `provisioning_status` (pending/failed/ready). Tenant access is gated on
 * provisioning_status in the hrms app's TenantResolver — a company is never
 * reachable by its own users until that is 'ready'.
 */
class CompanyProvisioningService
{
    private const RESERVED_CODES = [
        'admin', 'www', 'api', 'app', 'mail', 'ftp', 'root', 'mysql',
        'information_schema', 'performance_schema', 'sys', 'test',
        'hrms', 'platform', 'template', 'internal', 'static', 'cdn', 'ns1', 'ns2',
    ];

    /** Verified against hrms app/Database/Migrations (createTable calls). */
    private const REQUIRED_TENANT_TABLES = [
        'users', 'roles', 'permissions', 'role_permissions', 'user_roles', 'company_settings',
        'employees', 'departments', 'designations', 'branches',
        'attendance', 'leave_applications', 'leave_types',
        'payroll_runs', 'payroll_payslips',
        'audit_logs', 'login_logs', 'notifications',
    ];

    /** System roles created by the hrms app's TenantRbacSeeder (ROLE_PERMISSIONS keys). */
    private const REQUIRED_TENANT_ROLES = ['company-admin', 'hr-manager', 'manager', 'employee'];

    /** Anchor slugs from the hrms app's Config\TenantPermissions catalog, one or more per module. */
    private const REQUIRED_TENANT_PERMISSIONS = [
        'dashboard.view', 'users.view', 'roles.view', 'employee.view',
        'attendance.view', 'attendance.view.own', 'leave.view', 'leave.view.own',
        'payroll.view', 'payslip.download', 'reports.view',
    ];

    public function __construct(
        private CompanyModel $companies = new CompanyModel(),
        private CompanyDomainModel $domains = new CompanyDomainModel(),
        private CompanyDatabaseConnectionModel $connections = new CompanyDatabaseConnectionModel(),
        private ProvisioningLogModel $logs = new ProvisioningLogModel(),
        private AuditService $audit = new AuditService(),
        private SubscriptionService $subscriptions = new SubscriptionService(),
        private LicenseService $licenses = new LicenseService(),
    ) {
    }

    public function createCompany(array $input, int $platformUserId): int
    {
        $code = strtolower(trim((string) $input['code']));
        $this->assertValidCode($code);

        $subdomain = strtolower(trim((string) $input['subdomain']));
        if ($this->domains->isReserved($subdomain)) {
            throw new RuntimeException("\"{$subdomain}\" is a reserved subdomain and cannot be assigned to a company.");
        }

        $plan = (new PlanModel())->find((int) $input['plan_id']);
        if (! $plan || ! $plan['is_active']) {
            throw new RuntimeException('Selected plan is not available.');
        }

        $db = db_connect();
        $db->transStart();

        $this->companies->insert([
            'code'                 => $code,
            'name'                 => $input['name'],
            'status'               => 'trial',
            'provisioning_status'  => 'pending',
            'plan_id'              => $plan['id'],
            'employee_limit'       => (int) $input['employee_limit'],
            'timezone'             => $input['timezone'] ?: 'Asia/Kolkata',
            'currency'             => $input['currency'] ?: 'INR',
            'contact_name'         => $input['contact_name'] ?? null,
            'contact_email'        => $input['contact_email'] ?? null,
            'contact_phone'        => $input['contact_phone'] ?? null,
            'country'              => $input['country'] ?? null,
            'primary_admin_name'   => $input['admin_name'],
            'primary_admin_email'  => $input['admin_email'],
        ]);
        $companyId = $this->companies->getInsertID();

        $baseDomain = env('app.baseDomain', 'example.com');
        $domain     = $subdomain . '.' . $baseDomain;
        $this->domains->insert(['company_id' => $companyId, 'domain' => $domain, 'is_primary' => 1]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new RuntimeException('Failed to create company — transaction rolled back.');
        }

        $this->subscriptions->create([
            'company_id' => $companyId,
            'plan_id'    => $plan['id'],
            'starts_at'  => $input['starts_at'],
            'expires_at' => $input['expires_at'],
            'status'     => $input['status'],
            'created_by' => $platformUserId,
        ]);

        $this->audit->log('create', 'company', 'company', $companyId, null, ['code' => $code, 'name' => $input['name'], 'domain' => $domain], $companyId);

        return $companyId;
    }

    private function assertValidCode(string $code): void
    {
        if (preg_match('/^[a-z][a-z0-9]{1,20}$/', $code) !== 1) {
            throw new RuntimeException('Company code must start with a letter and contain only lowercase letters and numbers (2-21 characters, no spaces or symbols).');
        }
        if (in_array($code, self::RESERVED_CODES, true)) {
            throw new RuntimeException("\"{$code}\" is a reserved code and cannot be used.");
        }
    }

    public function hasVerifiedConnection(int $companyId): bool
    {
        $conn = $this->connections->forCompany($companyId);

        return $conn !== null && ! empty($conn['last_checked_at']);
    }

    /**
     * "Connect & Verify". Re-runs every read-only check server-side (the
     * browser's earlier "Test Connection" result is never trusted); on
     * success stores the encrypted connection and marks the company READY in
     * one platform-DB transaction. On a verification failure the company is
     * marked FAILED with the reason and nothing is stored, so the operator
     * can correct the details and retry. A blank password keeps the stored
     * one, so a re-save can never silently replace a working credential.
     */
    public function saveConnection(int $companyId, array $input): void
    {
        $company = $this->companies->find($companyId);
        if (! $company) {
            throw new RuntimeException('Company not found.');
        }
        if (! in_array($company['provisioning_status'], ['pending', 'failed', 'provisioning'], true)) {
            throw new RuntimeException('The database connection can only be changed before the company is ready or after a failed attempt.');
        }

        $existing = $this->connections->forCompany($companyId);
        $params   = $this->resolveParams($input, $existing);

        $taken = $this->connections
            ->where('db_host', $params['db_host'])
            ->where('db_port', $params['db_port'])
            ->where('db_name', $params['db_name'])
            ->where('company_id !=', $companyId)
            ->first();
        if ($taken) {
            throw new RuntimeException('That database is already assigned to another company.');
        }

        try {
            $this->runChecks($params, $companyId);
        } catch (RuntimeException $e) {
            $this->companies->update($companyId, [
                'provisioning_status' => 'failed',
                'provisioning_error'  => substr($e->getMessage(), 0, 250),
            ]);
            $this->audit->log('provision_failed', 'company', 'company', $companyId, null, ['error' => substr($e->getMessage(), 0, 250)], $companyId);

            throw $e;
        }

        $row = [
            'company_id'      => $companyId,
            'db_host'         => $params['db_host'],
            'db_port'         => $params['db_port'],
            'db_name'         => $params['db_name'],
            'db_username'     => $params['db_username'],
            'db_password_enc' => base64_encode(service('encrypter')->encrypt($params['db_password'])),
            'status'          => 'provisioned',
            'last_checked_at' => date('Y-m-d H:i:s'),
        ];

        $db = db_connect();
        $db->transStart();
        $existing ? $this->connections->update($existing['id'], $row) : $this->connections->insert($row);
        $this->companies->update($companyId, ['provisioning_status' => 'ready', 'provisioning_error' => null]);
        $db->transComplete();
        if ($db->transStatus() === false) {
            throw new RuntimeException('Could not save the database connection.');
        }

        $this->audit->log('db_connection_saved', 'company', 'company', $companyId, null, [
            'db_host' => $params['db_host'], 'db_port' => $params['db_port'],
            'db_name' => $params['db_name'], 'db_username' => $params['db_username'],
        ], $companyId);
        $this->audit->log('provision_ready', 'company', 'company', $companyId, null, ['provisioning_status' => 'ready'], $companyId);

        // Platform-database-only; never blocks READY. A failure is logged for the operator.
        try {
            $this->step($companyId, 'issue_license', function () use ($companyId) {
                $subscription = (new SubscriptionModel())->where('company_id', $companyId)->orderBy('id', 'DESC')->first();
                if ($subscription) {
                    $this->licenses->generate($companyId, (int) $subscription['plan_id'], $subscription['expires_at'], (int) session('platform_user_id'));
                }
            });
        } catch (Throwable) {
        }
    }

    /** Backs the "Test Connection" button: read-only checks, never stores anything. */
    public function testSuppliedConnection(int $companyId, array $input): array
    {
        $params = $this->resolveParams($input, $this->connections->forCompany($companyId));

        return $this->runChecks($params, null);
    }

    /**
     * The complete read-only verification. Every query is a SELECT.
     * When $companyId is given each check is recorded in provisioning_logs.
     *
     * @return array{present: string[], missing: string[]}
     */
    private function runChecks(array $p, ?int $companyId): array
    {
        $run = fn (string $name, callable $fn) => $companyId === null ? $fn() : $this->stepResult($companyId, $name, $fn);

        $run('verify_connection', fn () => $this->testConnection($p));
        $schema = $run('verify_schema', fn () => $this->assertTenantSchema($p));
        $run('verify_company_settings', fn () => $this->assertCompanySettings($p));
        $run('verify_rbac', fn () => $this->assertTenantRbac($p));

        return $schema;
    }

    /**
     * True only on a local development install: BOTH the configured base
     * domain and the host of the current request must be localhost / *.test /
     * *.localhost. Deliberately independent of CI_ENVIRONMENT.
     */
    public static function isLocalInstall(): bool
    {
        $isLocal = static fn (string $h): bool => $h === 'localhost' || str_ends_with($h, '.test') || str_ends_with($h, '.localhost');

        $base = strtolower(trim((string) env('app.baseDomain', '')));
        $host = strtolower((string) service('request')->getUri()->getHost());

        return $base !== '' && $host !== '' && $isLocal($base) && $isLocal($host);
    }

    /**
     * Normalises + validates raw form input. A blank password falls back to
     * the stored (encrypted) one when a connection row already exists.
     */
    private function resolveParams(array $input, ?array $existing): array
    {
        $host = trim((string) ($input['db_host'] ?? ''));
        $port = (int) ($input['db_port'] ?? 0);
        $name = trim((string) ($input['db_name'] ?? ''));
        $user = trim((string) ($input['db_username'] ?? ''));
        $pass = (string) ($input['db_password'] ?? '');

        if (preg_match('/^[A-Za-z0-9._-]{1,150}$/', $host) !== 1) {
            throw new RuntimeException('Database host is invalid.');
        }
        if ($port < 1 || $port > 65535) {
            throw new RuntimeException('Database port must be between 1 and 65535.');
        }
        if (preg_match('/^[A-Za-z0-9_]{1,64}$/', $name) !== 1) {
            throw new RuntimeException('Database name may contain only letters, numbers and underscores.');
        }
        if (preg_match('/^[A-Za-z0-9_.-]{1,80}$/', $user) !== 1) {
            throw new RuntimeException('Database username is invalid.');
        }
        $local = ($input['mode'] ?? 'live') === 'local';
        if ($local) {
            if (! self::isLocalInstall()) {
                throw new RuntimeException('Local (XAMPP) mode is only available on a local development install.');
            }
            if (! in_array(strtolower($host), ['127.0.0.1', 'localhost', '::1'], true)) {
                throw new RuntimeException('Local (XAMPP) mode can only connect to a database on this machine (127.0.0.1 / localhost).');
            }
        } elseif (strtolower($user) === 'root') {
            throw new RuntimeException('The MySQL root account must not be used. Use the dedicated tenant database user.');
        }
        if (strcasecmp($name, (string) db_connect()->getDatabase()) === 0) {
            throw new RuntimeException('The platform database cannot be used as a tenant database.');
        }

        if ($pass === '' && ! $local) {
            if (! $existing) {
                throw new RuntimeException('Database password is required.');
            }
            try {
                $pass = service('encrypter')->decrypt(base64_decode((string) $existing['db_password_enc']));
            } catch (Throwable) {
                throw new RuntimeException('Stored database password could not be read. Enter the password again.');
            }
        }

        return ['db_host' => $host, 'db_port' => $port, 'db_name' => $name, 'db_username' => $user, 'db_password' => $pass];
    }

    /**
     * Connects with the supplied credentials and confirms the database
     * exists. Throws RuntimeException with a safe message only — raw driver
     * messages are never surfaced and the password never appears in any
     * message.
     */
    private function connect(array $p): \mysqli
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $mysqli = mysqli_init();
        $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

        try {
            $mysqli->real_connect($p['db_host'], $p['db_username'], $p['db_password'], $p['db_name'], (int) $p['db_port']);
        } catch (\mysqli_sql_exception $e) {
            throw new RuntimeException(match ((int) $e->getCode()) {
                1045    => 'Invalid database username or password, or this user is not allowed from this host.',
                1044    => 'Database "' . $p['db_name'] . '" does not exist, or this user has no access to it. Create it manually and assign the user to it in Plesk.',
                1049    => 'Database "' . $p['db_name'] . '" does not exist. Create it manually in Plesk/phpMyAdmin first.',
                2002, 2003, 2005, 2006 => 'Database connection failed: could not reach the database server at ' . $p['db_host'] . ':' . $p['db_port'] . '.',
                default => 'Database connection failed (error ' . (int) $e->getCode() . ').',
            });
        }

        return $mysqli;
    }

    /**
     * Read-only check that the manually imported HRMS tenant schema is in
     * place. Never creates or alters anything and never runs migrations.
     * The required list is a subset of tables created by the HRMS app's own
     * migrations (app/Database/Migrations in the hrms project): the auth/RBAC
     * and company_settings tables plus one anchor table per core module, so a
     * partial or wrong import is caught.
     *
     * @return array{present: string[], missing: string[]}
     */
    public function validateTenantSchema(array $p): array
    {
        $mysqli = $this->connect($p);

        try {
            $stmt = $mysqli->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?');
            $stmt->bind_param('s', $p['db_name']);
            $stmt->execute();
            $existing = array_column($stmt->get_result()->fetch_all(MYSQLI_NUM), 0);
        } catch (\mysqli_sql_exception) {
            throw new RuntimeException('Could not read the table list of "' . $p['db_name'] . '".');
        } finally {
            $mysqli->close();
        }

        $existing = array_map('strtolower', $existing);
        $present  = array_values(array_filter(self::REQUIRED_TENANT_TABLES, fn ($t) => in_array($t, $existing, true)));
        $missing  = array_values(array_diff(self::REQUIRED_TENANT_TABLES, $present));

        return ['present' => $present, 'missing' => $missing];
    }

    /** @throws RuntimeException listing missing tables when the schema is incomplete */
    private function assertTenantSchema(array $p): array
    {
        $result = $this->validateTenantSchema($p);

        if ($result['missing'] !== []) {
            throw new RuntimeException('Tenant database schema is incomplete. Required HRMS table is missing — import the complete HRMS database schema. Missing tables: ' . implode(', ', $result['missing']) . '.');
        }

        return $result;
    }

    /**
     * Read-only: the imported database must already contain its
     * company_settings row. Never inserts or repairs it.
     */
    private function assertCompanySettings(array $p): void
    {
        $mysqli = $this->connect($p);

        try {
            $count = (int) $mysqli->query('SELECT COUNT(*) FROM company_settings')->fetch_row()[0];
        } catch (\mysqli_sql_exception) {
            throw new RuntimeException('Could not read the company_settings table of "' . $p['db_name'] . '".');
        } finally {
            $mysqli->close();
        }

        if ($count < 1) {
            throw new RuntimeException('company_settings has no row in "' . $p['db_name'] . '". Import the complete HRMS database (including its initial data) before continuing.');
        }
    }

    /**
     * Read-only check that the imported tenant database already carries the
     * RBAC data the HRMS seeder would have produced: the four system roles,
     * the anchor permissions, and role->permission mappings (company-admin
     * must hold every permission, the other roles at least one). Never
     * inserts, updates or repairs anything.
     *
     * @return string[] human-readable problems; empty when RBAC is complete
     */
    public function validateTenantRbac(array $p): array
    {
        $mysqli   = $this->connect($p);
        $problems = [];

        try {
            $roles = array_column($mysqli->query('SELECT slug FROM roles')->fetch_all(MYSQLI_NUM), 0);
            $perms = array_column($mysqli->query('SELECT slug FROM permissions')->fetch_all(MYSQLI_NUM), 0);
            $maps  = array_column(
                $mysqli->query('SELECT r.slug, COUNT(*) FROM role_permissions rp JOIN roles r ON r.id = rp.role_id GROUP BY r.slug')->fetch_all(MYSQLI_NUM),
                1,
                0
            );
        } catch (\mysqli_sql_exception) {
            throw new RuntimeException('Required RBAC table is missing or unreadable in "' . $p['db_name'] . '".');
        } finally {
            $mysqli->close();
        }

        if ($missing = array_diff(self::REQUIRED_TENANT_ROLES, $roles)) {
            $problems[] = 'roles: ' . implode(', ', $missing);
        }
        if ($missing = array_diff(self::REQUIRED_TENANT_PERMISSIONS, $perms)) {
            $problems[] = 'permissions: ' . implode(', ', $missing);
        }
        foreach (self::REQUIRED_TENANT_ROLES as $slug) {
            if (! in_array($slug, $roles, true)) {
                continue;
            }
            $count = (int) ($maps[$slug] ?? 0);
            if ($count === 0 || ($slug === 'company-admin' && $count < count($perms))) {
                $problems[] = "role-permission mappings: {$slug}";
            }
        }

        return $problems;
    }

    private function assertTenantRbac(array $p): void
    {
        $problems = $this->validateTenantRbac($p);

        if ($problems !== []) {
            throw new RuntimeException('Tenant RBAC data is incomplete. Import the complete HRMS tenant database before continuing. Missing ' . implode('; ', $problems) . '.');
        }
    }

    /** Read-only: connects, runs SELECT 1 and confirms the connection is on the named database. */
    private function testConnection(array $p): void
    {
        $mysqli = $this->connect($p);

        try {
            $mysqli->query('SELECT 1')->free();
            $current = (string) $mysqli->query('SELECT DATABASE()')->fetch_row()[0];
        } catch (\mysqli_sql_exception) {
            throw new RuntimeException('Database connection failed: could not run a query on "' . $p['db_name'] . '".');
        } finally {
            $mysqli->close();
        }

        if (strcasecmp($current, $p['db_name']) !== 0) {
            throw new RuntimeException('Database connection failed: connected to an unexpected database.');
        }
    }

    /**
     * Wraps one verification step with a started/completed/failed log row.
     * Messages recorded here are shown only to permissioned platform
     * admins (Companies -> Provisioning), never to a tenant end user —
     * see App\Filters\TenantResolver for the fully generic messages shown
     * there instead.
     */
    private function step(int $companyId, string $name, callable $fn): void
    {
        $this->stepResult($companyId, $name, $fn);
    }

    private function stepResult(int $companyId, string $name, callable $fn): mixed
    {
        $this->logs->record($companyId, $name, 'started');
        try {
            $result = $fn();
            $this->logs->record($companyId, $name, 'completed');

            return $result;
        } catch (Throwable $e) {
            $this->logs->record($companyId, $name, 'failed', substr($e->getMessage(), 0, 240));

            throw $e;
        }
    }
}
