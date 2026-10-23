<?php

use App\Services\TenantAdminLoginService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Change Admin Login, against TWO disposable databases: zz_sa_test (platform side) and
 * zz_sa_tenant (a company database installed from HRMS/hrms_tenant_install_template.sql).
 * Aborts unless connected to the disposable platform database.
 *
 * @internal
 */
final class TenantAdminLoginTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const PLATFORM = 'zz_sa_test';
    private const TENANT   = 'zz_sa_tenant';
    private const GOOD_PW  = 'Sup3r#Secret';

    protected $db;
    private $tenant;
    private int $companyId;
    private int $platformUserId;
    private int $adminId;
    private int $otherId;

    protected function setUp(): void
    {
        parent::setUp();

        $filters = config('Filters');
        $filters->globals['before'] = array_values(array_diff($filters->globals['before'], ['csrf']));

        $config = config('Database');
        $config->tests = array_merge($config->tests, [
            'DBDriver' => 'MySQLi', 'hostname' => '127.0.0.1', 'port' => (int) (getenv('SA_TEST_DB_PORT') ?: 3307),
            'username' => getenv('SA_TEST_DB_USER') ?: 'root', 'password' => getenv('SA_TEST_DB_PASS') ?: '',
            'database' => self::PLATFORM, 'DBPrefix' => '', 'strictOn' => true,
        ]);

        $this->db = db_connect();
        $this->assertSame(self::PLATFORM, $this->db->query('SELECT DATABASE() AS d')->getRow()->d, 'Refusing to run: not the disposable platform database.');

        $this->tenant = \Config\Database::connect(array_merge($config->tests, ['database' => self::TENANT]), false);
        $this->assertSame(self::TENANT, $this->tenant->query('SELECT DATABASE() AS d')->getRow()->d);

        // platform side
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['audit_logs', 'company_database_connections', 'company_domains', 'subscriptions', 'companies', 'platform_users'] as $t) {
            $this->db->query("TRUNCATE TABLE `{$t}`");
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $this->db->table('platform_users')->insert(['name' => 'Op', 'email' => 'op@platform.test', 'password_hash' => 'PLATFORM-HASH', 'status' => 'active']);
        $this->platformUserId = (int) $this->db->insertID();
        $this->company('admin@acme.test');

        // tenant side: clean users, one admin and one ordinary user
        $this->tenant->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['user_roles', 'password_history', 'remember_tokens', 'users'] as $t) {
            $this->tenant->query("TRUNCATE TABLE `{$t}`");
        }
        $this->tenant->query('SET FOREIGN_KEY_CHECKS = 1');

        $this->adminId = $this->tenantUser('Acme Admin', 'admin@acme.test', 'OldPass#1', 1);
        $this->otherId = $this->tenantUser('Emp One', 'emp@acme.test', 'EmpPass#1', 4);
    }

    private function company(?string $primaryAdminEmail, bool $withConnection = true, string $dbName = self::TENANT): void
    {
        $this->db->table('companies')->insert([
            'code' => 'acme', 'name' => 'Acme', 'status' => 'trial', 'provisioning_status' => 'ready', 'plan_id' => 1,
            'primary_admin_email' => $primaryAdminEmail,
        ]);
        $this->companyId = (int) $this->db->insertID();

        if ($withConnection) {
            $this->db->table('company_database_connections')->insert([
                'company_id' => $this->companyId, 'db_host' => '127.0.0.1', 'db_port' => (int) (getenv('SA_TEST_DB_PORT') ?: 3307),
                'db_name' => $dbName, 'db_username' => getenv('SA_TEST_DB_USER') ?: 'root',
                'db_password_enc' => base64_encode(service('encrypter')->encrypt((string) (getenv('SA_TEST_DB_PASS') ?: ''))),
                'status' => 'provisioned',
            ]);
        }
    }

    private function tenantUser(string $name, string $email, string $password, int $roleId, ?string $deletedAt = null): int
    {
        $this->tenant->table('users')->insert([
            'name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'status' => 'active', 'must_change_password' => 0, 'session_version' => 3, 'failed_login_attempts' => 4,
            'locked_until' => '2099-01-01 00:00:00', 'deleted_at' => $deletedAt,
        ]);
        $id = (int) $this->tenant->insertID();
        $this->tenant->table('user_roles')->insert(['user_id' => $id, 'role_id' => $roleId, 'is_primary' => 1]);

        return $id;
    }

    private function user(int $id): array
    {
        return $this->tenant->table('users')->where('id', $id)->get()->getRowArray();
    }

    private function svc(): TenantAdminLoginService
    {
        return new TenantAdminLoginService();
    }

    /** What HRMS's login does: find the active user by email, then password_verify(). */
    private function canLogin(string $email, string $password): bool
    {
        $u = $this->tenant->table('users')->where('email', $email)->where('deleted_at', null)->get()->getRowArray();

        return $u && $u['status'] === 'active' && password_verify($password, $u['password_hash']);
    }

    // ---- which account is changed ----

    public function testDescribeFindsTheCompanyAdminNotTheFirstUser(): void
    {
        $a = $this->svc()->describe($this->companyId);
        $this->assertSame($this->adminId, $a['id']);
        $this->assertSame('admin@acme.test', $a['email']);
        $this->assertArrayNotHasKey('password_hash', $a);
    }

    public function testAdminWithNonFirstIdIsStillFound(): void
    {
        $this->tenant->table('user_roles')->where('user_id', $this->adminId)->delete();   // admin1 loses the role
        $new = $this->tenantUser('New Admin', 'new@acme.test', 'NewPass#1', 1);

        $this->assertSame($new, $this->svc()->describe($this->companyId)['id']);
    }

    // ---- email / password / both ----

    public function testEmailOnly(): void
    {
        $oldHash = $this->user($this->adminId)['password_hash'];
        $r = $this->svc()->update($this->companyId, ' New@Acme.Test ', '', '');

        $this->assertSame(['email'], $r['changed']);
        $u = $this->user($this->adminId);
        $this->assertSame('new@acme.test', $u['email']);
        $this->assertSame($oldHash, $u['password_hash'], 'password untouched');
        $this->assertSame(3, (int) $u['session_version']);
        $this->assertSame('new@acme.test', $this->db->table('companies')->where('id', $this->companyId)->get()->getRow()->primary_admin_email);
    }

    public function testPasswordOnlyHashesAndForcesChange(): void
    {
        $r = $this->svc()->update($this->companyId, '', self::GOOD_PW, self::GOOD_PW);

        $this->assertSame(['password'], $r['changed']);
        $u = $this->user($this->adminId);
        $this->assertSame('admin@acme.test', $u['email']);
        $this->assertNotSame(self::GOOD_PW, $u['password_hash']);
        $this->assertTrue(password_verify(self::GOOD_PW, $u['password_hash']));
        $this->assertSame(1, (int) $u['must_change_password']);
        $this->assertSame(4, (int) $u['session_version'], 'other sessions are signed out');
        $this->assertSame(0, (int) $u['failed_login_attempts']);
        $this->assertNull($u['locked_until']);
        $this->assertSame(1, $this->tenant->table('password_history')->where('user_id', $this->adminId)->countAllResults());
    }

    public function testBothAndLoginWithNewCredentials(): void
    {
        $r = $this->svc()->update($this->companyId, 'owner@acme.test', self::GOOD_PW, self::GOOD_PW);

        $this->assertSame(['email', 'password'], $r['changed']);
        $this->assertTrue($this->canLogin('owner@acme.test', self::GOOD_PW));
        $this->assertFalse($this->canLogin('admin@acme.test', self::GOOD_PW), 'old email no longer signs in');
        $this->assertFalse($this->canLogin('owner@acme.test', 'OldPass#1'), 'old password no longer works');
    }

    // ---- validation ----

    public function testInvalidEmailRejected(): void
    {
        $r = $this->svc()->update($this->companyId, 'not-an-email', '', '');
        $this->assertArrayHasKey('email', $r['errors']);
        $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
    }

    public function testDuplicateEmailRejectedIncludingSoftDeletedUsers(): void
    {
        $r = $this->svc()->update($this->companyId, 'EMP@acme.test', '', '');
        $this->assertArrayHasKey('email', $r['errors']);

        $this->tenantUser('Gone', 'gone@acme.test', 'GonePass#1', 4, '2026-01-01 00:00:00');
        $r = $this->svc()->update($this->companyId, 'gone@acme.test', '', '');
        $this->assertArrayHasKey('email', $r['errors'], 'the unique index also covers soft-deleted rows');
        $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
    }

    public function testPasswordMismatchWeakAndReusedRejected(): void
    {
        $hash = $this->user($this->adminId)['password_hash'];

        $this->assertArrayHasKey('password_confirm', $this->svc()->update($this->companyId, '', self::GOOD_PW, 'different')['errors']);
        $this->assertArrayHasKey('password', $this->svc()->update($this->companyId, '', 'short', 'short')['errors']);
        $this->assertArrayHasKey('password', $this->svc()->update($this->companyId, '', 'alllowercase1!', 'alllowercase1!')['errors']);
        $this->assertArrayHasKey('password', $this->svc()->update($this->companyId, '', 'OldPass#1', 'OldPass#1')['errors'], 'current password cannot be reused');
        $this->assertSame($hash, $this->user($this->adminId)['password_hash']);
    }

    public function testNothingEnteredIsAnError(): void
    {
        $this->assertArrayHasKey('email', $this->svc()->update($this->companyId, '', '', '')['errors']);
    }

    public function testSameEmailIsNoChange(): void
    {
        $this->assertSame([], $this->svc()->update($this->companyId, 'ADMIN@acme.test', '', '')['changed']);
    }

    // ---- isolation ----

    public function testOtherUsersAndPlatformUserAreUntouched(): void
    {
        $other    = $this->user($this->otherId);
        $platform = $this->db->table('platform_users')->where('id', $this->platformUserId)->get()->getRowArray();

        $this->svc()->update($this->companyId, 'owner@acme.test', self::GOOD_PW, self::GOOD_PW);

        $this->assertSame($other, $this->user($this->otherId));
        $this->assertSame($platform, $this->db->table('platform_users')->where('id', $this->platformUserId)->get()->getRowArray());
        $this->assertSame(2, $this->tenant->table('users')->countAllResults());
    }

    // ---- ambiguity / missing ----

    public function testNoCompanyAdminStopsWithoutWriting(): void
    {
        $this->tenant->table('user_roles')->where('user_id', $this->adminId)->delete();

        $this->expectExceptionMessage('no administrator account');
        try {
            $this->svc()->update($this->companyId, 'x@acme.test', '', '');
        } finally {
            $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
        }
    }

    public function testSeveralAdminsWithoutAUniqueMatchStops(): void
    {
        $this->tenantUser('Second Admin', 'second@acme.test', 'SecondPw#1', 1);
        $this->db->table('companies')->where('id', $this->companyId)->update(['primary_admin_email' => 'nobody@acme.test']);

        try {
            $this->svc()->update($this->companyId, 'x@acme.test', '', '');
            $this->fail('Expected the ambiguity to stop the change');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('cannot be identified safely', $e->getMessage());
        }
        $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
    }

    public function testSeveralAdminsResolvedByPrimaryAdminEmail(): void
    {
        $second = $this->tenantUser('Second Admin', 'second@acme.test', 'SecondPw#1', 1);

        $this->svc()->update($this->companyId, 'owner@acme.test', '', '');

        $this->assertSame('owner@acme.test', $this->user($this->adminId)['email']);
        $this->assertSame('second@acme.test', $this->user($second)['email']);
    }

    // ---- connection failures ----

    public function testMissingConnectionFailsSafely(): void
    {
        $this->db->table('company_database_connections')->truncate();

        $this->expectExceptionMessage('no provisioned tenant database connection');
        $this->svc()->update($this->companyId, 'x@acme.test', '', '');
    }

    public function testUnreachableTenantDatabaseFailsSafely(): void
    {
        $this->db->table('company_database_connections')->where('company_id', $this->companyId)->update(['db_name' => 'zz_does_not_exist']);

        try {
            $this->svc()->update($this->companyId, 'x@acme.test', '', '');
            $this->fail('Expected a connection failure');
        } catch (\Throwable $e) {
            $this->assertStringNotContainsString('password', strtolower($e->getMessage()));
        }
        $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
    }

    public function testFailedWriteRollsBackEverything(): void
    {
        $this->tenant->query('RENAME TABLE password_history TO password_history_x');

        try {
            $this->svc()->update($this->companyId, 'owner@acme.test', self::GOOD_PW, self::GOOD_PW);
            $this->fail('Expected the write to fail');
        } catch (\Throwable $e) {
            // expected
        } finally {
            $this->tenant->query('RENAME TABLE password_history_x TO password_history');
        }

        $u = $this->user($this->adminId);
        $this->assertSame('admin@acme.test', $u['email'], 'email change rolled back');
        $this->assertTrue(password_verify('OldPass#1', $u['password_hash']), 'password change rolled back');
    }

    // ---- routes: authorization, confirmation, audit ----

    private function session(array $perms): array
    {
        return ['platform_user_id' => $this->platformUserId, 'platform_permissions' => $perms];
    }

    public function testUnauthorizedUserGets403AndNothingChanges(): void
    {
        $s = $this->session(['company.view', 'company.edit', 'company.settings']);

        $this->assertSame(403, $this->withSession($s)->get("companies/{$this->companyId}/admin-login")->response()->getStatusCode());
        $r = $this->withSession($s)->post("companies/{$this->companyId}/admin-login", ['email' => 'x@acme.test', 'confirm' => '1']);
        $this->assertSame(403, $r->response()->getStatusCode());
        $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
    }

    public function testAnonymousIsRedirectedToLogin(): void
    {
        $this->assertTrue($this->post("companies/{$this->companyId}/admin-login", ['email' => 'x@acme.test', 'confirm' => '1'])->isRedirect());
        $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
    }

    public function testFormShowsCompanyAndCurrentAdminEmail(): void
    {
        $r = $this->withSession($this->session(['company.view', 'company.admin_login']))->get("companies/{$this->companyId}/admin-login");
        $r->assertOK();
        $this->assertStringContainsString('Change Admin Login', $r->getBody());
        $this->assertStringContainsString('admin@acme.test', $r->getBody());
        $this->assertStringNotContainsString('password_hash', $r->getBody());
    }

    public function testConfirmationRequired(): void
    {
        $s = $this->session(['company.view', 'company.admin_login']);
        $this->withSession($s)->post("companies/{$this->companyId}/admin-login", ['email' => 'x@acme.test'])->assertRedirect();
        $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
    }

    public function testSuccessfulRouteAuditsWithoutThePassword(): void
    {
        $s = $this->session(['company.view', 'company.admin_login']);
        $r = $this->withSession($s)->post("companies/{$this->companyId}/admin-login", [
            'email' => 'owner@acme.test', 'password' => self::GOOD_PW, 'password_confirm' => self::GOOD_PW, 'confirm' => '1',
        ]);
        $r->assertRedirectTo(site_url("companies/{$this->companyId}"));
        $this->assertTrue($this->canLogin('owner@acme.test', self::GOOD_PW));

        $audit = $this->db->table('audit_logs')->where('module', 'tenant_admin_login')->get()->getResultArray();
        $this->assertCount(1, $audit);
        $this->assertSame($this->platformUserId, (int) $audit[0]['platform_user_id']);
        $this->assertSame($this->companyId, (int) $audit[0]['company_id']);
        $dump = json_encode($audit);
        $this->assertStringNotContainsString(self::GOOD_PW, $dump);
        $this->assertStringNotContainsString('$2y$', $dump, 'no hashes in the audit trail');
        $this->assertStringContainsString('password', $dump);   // records THAT it changed, not the value
    }

    public function testRouteShowsValidationErrorsWithoutLeakingInput(): void
    {
        $s = $this->session(['company.view', 'company.admin_login']);
        $this->withSession($s)->post("companies/{$this->companyId}/admin-login", [
            'email' => 'emp@acme.test', 'password' => self::GOOD_PW, 'password_confirm' => self::GOOD_PW, 'confirm' => '1',
        ])->assertRedirect();

        $this->assertSame('admin@acme.test', $this->user($this->adminId)['email']);
        $this->assertTrue(password_verify('OldPass#1', $this->user($this->adminId)['password_hash']), 'a rejected email must not apply the password either');
    }
}
