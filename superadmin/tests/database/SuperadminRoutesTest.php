<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Route -> filter -> controller -> service -> database, end to end, as a logged-in platform
 * user, against the DISPOSABLE database zz_sa_test (aborts unless connected to it).
 * Covers the Dashboard, Companies and Subscriptions screens and the license actions.
 *
 * @internal
 */
final class SuperadminRoutesTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private const DB = 'zz_sa_test';

    protected $db;
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $config = config('Database');
        $config->tests = array_merge($config->tests, [
            'DBDriver' => 'MySQLi',
            'hostname' => getenv('SA_TEST_DB_HOST') ?: '127.0.0.1',
            'port'     => (int) (getenv('SA_TEST_DB_PORT') ?: 3307),
            'username' => getenv('SA_TEST_DB_USER') ?: 'root',
            'password' => getenv('SA_TEST_DB_PASS') ?: '',
            'database' => self::DB,
            'DBPrefix' => '',
            'strictOn' => true,
        ]);

        // Forms are posted without a browser, so the CSRF token step is switched off for these tests only.
        $filters = config('Filters');
        $filters->globals['before'] = array_values(array_diff($filters->globals['before'], ['csrf']));

        $this->db = db_connect();
        $this->assertSame(self::DB, $this->db->query('SELECT DATABASE() AS d')->getRow()->d, 'Refusing to run: not the disposable test database.');

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['subscription_history', 'licenses', 'audit_logs', 'subscriptions', 'company_domains', 'company_database_connections', 'companies', 'platform_users'] as $t) {
            $this->db->query("TRUNCATE TABLE `{$t}`");
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $this->db->table('platform_users')->insert(['name' => 'Tester', 'email' => 't@test.local', 'password_hash' => 'x', 'status' => 'active']);
        $this->userId = (int) $this->db->insertID();
    }

    private function session(array $perms = null): array
    {
        return [
            'platform_user_id'     => $this->userId,
            'platform_permissions' => $perms ?? [
                'company.view', 'company.create', 'company.edit', 'company.activate', 'company.suspend', 'company.delete',
                'subscription.view', 'subscription.create', 'subscription.edit', 'subscription.extend', 'subscription.suspend',
                'plan.view', 'audit.view',
            ],
        ];
    }

    private function company(string $code, ?array $sub = null): int
    {
        $this->db->table('companies')->insert([
            'code' => $code, 'name' => 'Co ' . $code, 'status' => 'trial', 'provisioning_status' => 'ready', 'plan_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $this->db->table('company_domains')->insert(['company_id' => $id, 'domain' => $code . '.test', 'is_primary' => 1]);

        if ($sub) {
            $this->db->table('subscriptions')->insert(['company_id' => $id, 'plan_id' => 1, 'status' => $sub[0], 'starts_at' => $sub[1], 'expires_at' => $sub[2]]);
            $this->db->table('companies')->where('id', $id)->update(['current_subscription_id' => (int) $this->db->insertID(), 'status' => $sub[0]]);
        }

        return $id;
    }

    private function row(string $table, int $id): array
    {
        return $this->db->table($table)->where('id', $id)->get()->getRowArray();
    }

    // ---- access control ----

    public function testAnonymousIsRedirectedToLogin(): void
    {
        foreach (['companies', 'subscriptions', 'dashboard'] as $path) {
            $r = $this->get($path);
            $this->assertTrue($r->isRedirect(), $path);
            $this->assertStringContainsString('login', $r->getRedirectUrl());
        }
    }

    public function testMissingPermissionIs403(): void
    {
        $r = $this->withSession($this->session(['dashboard.view']))->get('companies');
        $this->assertSame(403, $r->response()->getStatusCode());
    }

    // ---- dashboard / companies list ----

    public function testDashboardShowsDateAwareCounts(): void
    {
        $this->company('live', ['active', '2026-01-01', date('Y-m-d', strtotime('+30 days'))]);
        $this->company('old', ['active', '2026-01-01', date('Y-m-d', strtotime('-1 day'))]);
        $this->company('tri', ['trial', '2026-01-01', date('Y-m-d')]);

        $r = $this->withSession($this->session())->get('dashboard');
        $r->assertOK();
        $html = $r->getBody();
        $this->assertMatchesRegularExpression('/label">Active<\/div>\s*<div class="value[^"]*">1</', $html);
        $this->assertMatchesRegularExpression('/label">Trial<\/div>\s*<div class="value[^>]*>1</', $html);
        $this->assertMatchesRegularExpression('/label">Expired<\/div>\s*<div class="value[^"]*">1</', $html);
    }

    public function testCompaniesListFilterSearchAndPagination(): void
    {
        $this->company('live', ['active', '2026-01-01', date('Y-m-d', strtotime('+30 days'))]);
        $this->company('old', ['active', '2026-01-01', date('Y-m-d', strtotime('-1 day'))]);
        $s = $this->session();

        $active = $this->withSession($s)->get('companies', ['status' => 'active']);
        $active->assertOK();
        $this->assertStringContainsString('Co live', $active->getBody());
        $this->assertStringNotContainsString('Co old', $active->getBody());

        $expired = $this->withSession($s)->get('companies', ['status' => 'expired']);
        $this->assertStringContainsString('Co old', $expired->getBody());
        $this->assertStringNotContainsString('Co live', $expired->getBody());

        $search = $this->withSession($s)->get('companies', ['q' => 'live']);
        $this->assertStringContainsString('Co live', $search->getBody());
        $this->assertStringNotContainsString('Co old', $search->getBody());

        for ($i = 0; $i < 16; $i++) {
            $this->company('bulk' . $i);
        }
        $page1 = $this->withSession($s)->get('companies');
        $page2 = $this->withSession($s)->get('companies', ['page_companies' => 2]);
        $page1->assertOK();
        $page2->assertOK();
        $this->assertNotSame($page1->getBody(), $page2->getBody(), 'second page must show different rows');
    }

    // ---- company actions ----

    public function testSuspendThenActivateCompany(): void
    {
        $id = $this->company('acme', ['trial', '2026-01-01', date('Y-m-d', strtotime('+30 days'))]);
        $s  = $this->session();

        $this->withSession($s)->post("companies/{$id}/suspend")->assertRedirect();
        $this->assertSame('suspended', $this->row('companies', $id)['status']);

        $this->withSession($s)->post("companies/{$id}/activate")->assertRedirect();
        $this->assertSame('trial', $this->row('companies', $id)['status'], 'activation follows the subscription state');
    }

    public function testCompanyActionsRequirePermission(): void
    {
        $id = $this->company('acme');
        $r  = $this->withSession($this->session(['company.view']))->post("companies/{$id}/suspend");
        $this->assertSame(403, $r->response()->getStatusCode());
        $this->assertSame('trial', $this->row('companies', $id)['status']);
    }

    // ---- subscription actions ----

    public function testSubscriptionCreateValidatesAndCreates(): void
    {
        $c = $this->company('acme');
        $s = $this->session();
        $good = ['company_id' => $c, 'plan_id' => 2, 'starts_at' => '2026-10-01', 'expires_at' => '2026-12-31', 'status' => 'active'];

        $bad = $this->withSession($s)->post('subscriptions', ['expires_at' => '2026-09-01'] + $good);
        $bad->assertOK();   // form re-rendered with an error, not a redirect
        $this->assertStringContainsString('before the start date', $bad->getBody());
        $this->assertSame(0, $this->db->table('subscriptions')->countAllResults());

        $ok = $this->withSession($s)->post('subscriptions', $good);
        $this->assertTrue($ok->isRedirect());
        $this->assertSame(1, $this->db->table('subscriptions')->countAllResults());
        $co = $this->row('companies', $c);
        $this->assertSame('active', $co['status']);
        $this->assertSame(2, (int) $co['plan_id']);
    }

    public function testExtendSuspendCancelPlanChangeAndHistory(): void
    {
        $c = $this->company('acme', ['trial', '2026-01-01', date('Y-m-d', strtotime('+10 days'))]);
        $sid = (int) $this->row('companies', $c)['current_subscription_id'];
        $s = $this->session();

        $newExpiry = date('Y-m-d', strtotime('+90 days'));
        $this->withSession($s)->post("subscriptions/{$sid}/extend", ['expires_at' => $newExpiry])->assertRedirect();
        $this->assertSame($newExpiry, $this->row('subscriptions', $sid)['expires_at']);

        $bad = $this->withSession($s)->post("subscriptions/{$sid}/extend", ['expires_at' => 'nonsense']);
        $this->assertTrue($bad->isRedirect());
        $this->assertSame($newExpiry, $this->row('subscriptions', $sid)['expires_at'], 'invalid date leaves the record untouched');

        $this->withSession($s)->post("subscriptions/{$sid}", ['plan_id' => 3, 'employee_limit_override' => ''])->assertRedirect();
        $this->assertSame(3, (int) $this->row('companies', $c)['plan_id']);

        $this->withSession($s)->post("subscriptions/{$sid}/suspend", ['remarks' => 'unpaid'])->assertRedirect();
        $this->assertSame('suspended', $this->row('subscriptions', $sid)['status']);
        $this->assertSame('suspended', $this->row('companies', $c)['status']);

        $this->withSession($s)->post("subscriptions/{$sid}/cancel")->assertRedirect();
        $this->assertSame('cancelled', $this->row('companies', $c)['status']);

        $h = $this->withSession($s)->get("subscriptions/{$sid}/history");
        $h->assertOK();
        $this->assertGreaterThanOrEqual(4, $this->db->table('subscription_history')->where('subscription_id', $sid)->countAllResults());
    }

    public function testMissingSubscriptionIdsFailCleanly(): void
    {
        $s = $this->session();
        $r = $this->withSession($s)->post('subscriptions/99999/extend', ['expires_at' => date('Y-m-d', strtotime('+5 days'))]);
        $this->assertTrue($r->isRedirect());

        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);   // CI turns this into the 404 page
        $this->withSession($s)->get('subscriptions/99999/history');
    }

    public function testSubscriptionListFiltersByCompanyAndStatus(): void
    {
        $a = $this->company('alpha', ['trial', '2026-01-01', '2027-01-01']);
        $this->company('beta', ['active', '2026-01-01', '2027-01-01']);
        $s = $this->session();

        $r = $this->withSession($s)->get('subscriptions', ['company_id' => $a]);
        $r->assertOK();
        // The company dropdown lists every company, so look only at the table rows.
        $this->assertStringContainsString('fw-semibold">Co alpha', $r->getBody());
        $this->assertStringNotContainsString('fw-semibold">Co beta', $r->getBody());

        $r = $this->withSession($s)->get('subscriptions', ['status' => 'active']);
        $this->assertStringContainsString('fw-semibold">Co beta', $r->getBody());
        $this->assertStringNotContainsString('fw-semibold">Co alpha', $r->getBody());
    }

    // ---- license actions ----

    public function testLicenseRenewAndRevoke(): void
    {
        $c = $this->company('acme', ['trial', '2026-01-01', date('Y-m-d', strtotime('+30 days'))]);
        (new \App\Services\LicenseService())->generate($c, 1, date('Y-m-d', strtotime('+30 days')), $this->userId);
        $s = $this->session(['company.view', 'company.edit', 'company.suspend']);

        $subBefore = $this->row('subscriptions', (int) $this->row('companies', $c)['current_subscription_id'])['expires_at'];

        $this->withSession($s)->post("companies/{$c}/license/renew", ['months' => 12])->assertRedirect();
        $this->assertSame(2, $this->db->table('licenses')->where('company_id', $c)->countAllResults());
        $this->assertSame($subBefore, $this->row('subscriptions', (int) $this->row('companies', $c)['current_subscription_id'])['expires_at'], 'license renewal must not move the subscription expiry');

        $this->withSession($s)->post("companies/{$c}/license/revoke", ['reason' => ''])->assertRedirect();
        $this->assertSame(0, $this->db->table('licenses')->where(['company_id' => $c, 'status' => 'revoked'])->countAllResults(), 'reason is required');

        $this->withSession($s)->post("companies/{$c}/license/revoke", ['reason' => 'test'])->assertRedirect();
        $this->assertSame(1, $this->db->table('licenses')->where(['company_id' => $c, 'status' => 'revoked'])->countAllResults());
    }

    // ---- add / edit company ----

    public function testAddCompanyValidationDuplicatesAndSuccess(): void
    {
        $s = $this->session();
        $good = [
            'name' => 'Zeta Works', 'code' => 'zeta', 'subdomain' => 'zeta', 'plan_id' => 1, 'status' => 'trial',
            'employee_limit' => 25, 'starts_at' => '2026-10-01', 'expires_at' => '2026-12-31',
            'admin_name' => 'Zed Admin', 'admin_email' => 'zed@example.com',
        ];

        $badDates = $this->withSession($s)->post('companies', ['expires_at' => '2026-09-01'] + $good);
        $badDates->assertOK();
        $this->assertStringContainsString('before the start date', $badDates->getBody());
        $this->assertSame(0, $this->db->table('companies')->countAllResults(), 'nothing saved when validation fails');

        $reserved = $this->withSession($s)->post('companies', ['subdomain' => 'admin', 'code' => 'zeta2'] + $good);
        $reserved->assertOK();
        $this->assertStringContainsString('reserved', $reserved->getBody());
        $this->assertSame(0, $this->db->table('companies')->countAllResults());

        $ok = $this->withSession($s)->post('companies', $good);
        $this->assertTrue($ok->isRedirect(), 'form errors: ' . implode(' | ', array_map('trim', preg_match_all('/invalid-feedback">([^<]*)</', $ok->getBody(), $m) ? $m[1] : [substr(strip_tags($ok->getBody()), 0, 300)])));
        $company = $this->db->table('companies')->where('code', 'zeta')->get()->getRowArray();
        $this->assertSame('pending', $company['provisioning_status']);
        $this->assertNotEmpty($company['current_subscription_id']);
        $this->assertSame(1, $this->db->table('company_domains')->where('company_id', $company['id'])->countAllResults());

        $dup = $this->withSession($s)->post('companies', ['subdomain' => 'zeta-other'] + $good);
        $dup->assertOK();   // duplicate code -> form re-rendered, not saved
        $this->assertSame(1, $this->db->table('companies')->countAllResults());
    }

    public function testEditCompanyUpdatesAndValidates(): void
    {
        $id = $this->company('acme');
        $s  = $this->session();

        $bad = $this->withSession($s)->post("companies/{$id}", ['name' => '', 'plan_id' => 1, 'employee_limit' => 5]);
        $bad->assertOK();
        $this->assertSame('Co acme', $this->row('companies', $id)['name']);

        $this->withSession($s)->post("companies/{$id}", ['name' => 'Acme Renamed', 'plan_id' => 1, 'employee_limit' => 9, 'timezone' => 'Asia/Kolkata', 'currency' => 'INR'])->assertRedirect();
        $this->assertSame('Acme Renamed', $this->row('companies', $id)['name']);
    }
}
