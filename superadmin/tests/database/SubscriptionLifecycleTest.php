<?php

use App\Services\SubscriptionService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Subscription lifecycle against a DISPOSABLE MySQL database (zz_sa_test: a no-data
 * copy of the platform schema plus the plans rows). Every test aborts unless the
 * connection really is that database, so real company data can never be touched.
 *
 * Setup (once):  CREATE DATABASE zz_sa_test; load the platform schema into it;
 *                copy the `plans` rows. Override with env SA_TEST_DB_* if needed.
 *
 * @internal
 */
final class SubscriptionLifecycleTest extends CIUnitTestCase
{
    private const DB = 'zz_sa_test';

    protected $db;
    private SubscriptionService $svc;

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

        $this->db = db_connect();
        $name     = $this->db->query('SELECT DATABASE() AS d')->getRow()->d;
        $this->assertSame(self::DB, $name, 'Refusing to run: not connected to the disposable test database.');

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['subscription_history', 'licenses', 'audit_logs', 'subscriptions', 'company_domains', 'companies'] as $t) {
            $this->db->query("TRUNCATE TABLE `{$t}`");
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $this->svc = new SubscriptionService();
    }

    private function company(string $code, int $planId = 1, string $status = 'trial'): int
    {
        $this->db->table('companies')->insert([
            'code' => $code, 'name' => strtoupper($code), 'status' => $status, 'provisioning_status' => 'ready',
            'plan_id' => $planId, 'employee_limit' => 0,
        ]);
        $id = (int) $this->db->insertID();
        $this->db->table('company_domains')->insert(['company_id' => $id, 'domain' => $code . '.test', 'is_primary' => 1]);

        return $id;
    }

    private function sub(int $companyId, array $o = []): int
    {
        return $this->svc->create($o + [
            'company_id' => $companyId, 'plan_id' => 1,
            'starts_at'  => date('Y-m-d', strtotime('-10 days')), 'expires_at' => date('Y-m-d', strtotime('+20 days')),
            'status'     => 'trial',
        ]);
    }

    private function row(string $table, int $id): array
    {
        return $this->db->table($table)->where('id', $id)->get()->getRowArray();
    }

    private function future(int $days = 30): string
    {
        return date('Y-m-d', strtotime("+{$days} days"));
    }

    // ---- create / current subscription pointer ----

    public function testCreateMakesSubscriptionCurrentAndSyncsCompany(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c, ['plan_id' => 2, 'status' => 'active']);

        $co = $this->row('companies', $c);
        $this->assertSame($id, (int) $co['current_subscription_id']);
        $this->assertSame(2, (int) $co['plan_id']);
        $this->assertSame('active', $co['status']);
        $this->assertSame(100, (int) $co['employee_limit']);
        $this->assertSame($c, (int) $this->row('subscriptions', $id)['company_id']);
        $this->assertSame(1, $this->db->table('subscription_history')->where('subscription_id', $id)->countAllResults());
    }

    public function testNewSubscriptionDoesNotTouchOtherCompany(): void
    {
        $a  = $this->company('alpha');
        $b  = $this->company('beta');
        $sa = $this->sub($a);
        $this->sub($b, ['plan_id' => 3]);

        $this->assertSame($sa, (int) $this->row('companies', $a)['current_subscription_id']);
        $this->assertSame(1, (int) $this->row('companies', $a)['plan_id']);
    }

    public function testCreateRejectsMissingCompany(): void
    {
        $this->expectExceptionMessage('Company not found');
        $this->sub(99999);
    }

    public function testCreateRejectsMissingPlanAndWritesNothing(): void
    {
        $c = $this->company('alpha');

        try {
            $this->sub($c, ['plan_id' => 99999]);
            $this->fail('Expected exception');
        } catch (RuntimeException $e) {
            $this->assertSame('Plan not found.', $e->getMessage());
        }
        $this->assertSame(0, $this->db->table('subscriptions')->countAllResults());
    }

    public function testCreateRejectsExpiryBeforeStart(): void
    {
        $c = $this->company('alpha');

        try {
            $this->sub($c, ['starts_at' => '2026-10-05', 'expires_at' => '2026-10-04']);
            $this->fail('Expected exception');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('before the start date', $e->getMessage());
        }
        $this->assertSame(0, $this->db->table('subscriptions')->countAllResults());
    }

    public function testExpiryOnStartDateIsAllowed(): void
    {
        $this->assertNull(SubscriptionService::dateRangeError('2026-10-05', '2026-10-05'));
        $this->assertNotNull(SubscriptionService::dateRangeError('2026-10-05', 'not-a-date'));
    }

    // ---- renewal / extend ----

    public function testExtendExpiredSubscriptionReactivatesAndSyncsCompany(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c, ['starts_at' => '2026-01-01', 'expires_at' => '2026-01-31', 'status' => 'expired']);

        $new = $this->future(30);
        $this->svc->extend($id, $new);

        $s = $this->row('subscriptions', $id);
        $this->assertSame($new, $s['expires_at']);
        $this->assertSame('active', $s['status']);
        $this->assertSame('active', $this->row('companies', $c)['status']);
        $this->assertSame(1, $this->db->table('subscription_history')->where(['subscription_id' => $id, 'action' => 'extended'])->countAllResults());
    }

    public function testExtendTrialKeepsTrialStatus(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c);
        $this->svc->extend($id, $this->future(40));

        $this->assertSame('trial', $this->row('subscriptions', $id)['status']);
    }

    public function testExtendSuspendedSubscriptionRestoresAccess(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c, ['status' => 'suspended']);
        $this->svc->extend($id, $this->future(40));

        $this->assertSame('active', $this->row('companies', $c)['status']);
    }

    public function testExtendCancelledSubscriptionStaysCancelled(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c);
        $this->svc->changeStatus($id, 'cancelled');
        $this->svc->extend($id, $this->future(40));

        $this->assertSame('cancelled', $this->row('subscriptions', $id)['status']);
        $this->assertSame('cancelled', $this->row('companies', $c)['status']);
    }

    public function testExtendRejectsInvalidDateWithoutChangingAnything(): void
    {
        $c      = $this->company('alpha');
        $id     = $this->sub($c);
        $before = $this->row('subscriptions', $id);

        try {
            $this->svc->extend($id, 'garbage');
            $this->fail('Expected exception');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('not a valid date', $e->getMessage());
        }
        $this->assertSame($before['expires_at'], $this->row('subscriptions', $id)['expires_at']);
    }

    public function testExtendRejectsPastDate(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c);

        $this->expectExceptionMessage('cannot be in the past');
        $this->svc->extend($id, date('Y-m-d', strtotime('-1 day')));
    }

    public function testExtendToTodayIsAllowed(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c);
        $this->svc->extend($id, date('Y-m-d'));

        $this->assertSame(date('Y-m-d'), $this->row('subscriptions', $id)['expires_at']);
    }

    public function testExtendMissingSubscriptionFailsCleanly(): void
    {
        $this->expectExceptionMessage('Subscription not found');
        $this->svc->extend(99999, $this->future());
    }

    public function testExtendNonCurrentSubscriptionDoesNotChangeCompany(): void
    {
        $c   = $this->company('alpha');
        $old = $this->sub($c);
        $new = $this->sub($c, ['plan_id' => 2, 'status' => 'active']);

        $this->svc->extend($old, $this->future(50));

        $co = $this->row('companies', $c);
        $this->assertSame($new, (int) $co['current_subscription_id']);
        $this->assertSame(2, (int) $co['plan_id']);
    }

    public function testExtendRenewsExistingLicenseOnlyForCurrentSubscription(): void
    {
        $c = $this->company('alpha');
        $id = $this->sub($c);
        (new \App\Services\LicenseService())->generate($c, 1, $this->future(10), 1);
        $this->assertSame(1, $this->db->table('licenses')->where('company_id', $c)->countAllResults());

        $this->svc->extend($id, $this->future(90));

        $this->assertSame(2, $this->db->table('licenses')->where('company_id', $c)->countAllResults());
    }

    public function testExtendWithoutLicenseDoesNotCreateOne(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c);
        $this->svc->extend($id, $this->future(90));

        $this->assertSame(0, $this->db->table('licenses')->where('company_id', $c)->countAllResults());
    }

    public function testExtendDoesNotRenewRevokedLicense(): void
    {
        $c   = $this->company('alpha');
        $id  = $this->sub($c);
        $lic = new \App\Services\LicenseService();
        $row = $lic->generate($c, 1, $this->future(10), 1);
        $lic->revoke((int) $row['id'], 1, 'test');

        $this->svc->extend($id, $this->future(90));

        $this->assertSame(1, $this->db->table('licenses')->where('company_id', $c)->countAllResults());
    }

    // ---- suspend / cancel ----

    public function testSuspendAndCancelSyncCompanyStatus(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c, ['status' => 'active']);

        $this->svc->changeStatus($id, 'suspended', 'unpaid');
        $this->assertSame('suspended', $this->row('companies', $c)['status']);
        $this->assertSame('unpaid', $this->row('subscriptions', $id)['remarks']);

        $this->svc->changeStatus($id, 'cancelled');
        $this->assertSame('cancelled', $this->row('companies', $c)['status']);
    }

    public function testChangeStatusMissingSubscriptionFailsCleanly(): void
    {
        $this->expectExceptionMessage('Subscription not found');
        $this->svc->changeStatus(99999, 'suspended');
    }

    // ---- plan change ----

    public function testChangePlanSyncsCompanyPlanAndEmployeeLimit(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c, ['status' => 'active']);

        $this->svc->changePlan($id, 3, null);
        $co = $this->row('companies', $c);
        $this->assertSame(3, (int) $co['plan_id']);
        $this->assertSame(500, (int) $co['employee_limit']);
        $this->assertSame($id, (int) $co['current_subscription_id']);

        $this->svc->changePlan($id, 3, 42);
        $this->assertSame(42, (int) $this->row('companies', $c)['employee_limit']);
    }

    public function testChangePlanRejectsMissingPlanAndBadOverride(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c);

        foreach ([[99999, null, 'Plan not found'], [2, 0, 'at least 1']] as [$plan, $ov, $msg]) {
            try {
                $this->svc->changePlan($id, $plan, $ov);
                $this->fail('Expected exception');
            } catch (RuntimeException $e) {
                $this->assertStringContainsString($msg, $e->getMessage());
            }
        }
        $this->assertSame(1, (int) $this->row('subscriptions', $id)['plan_id']);
        $this->assertSame(1, (int) $this->row('companies', $c)['plan_id']);
    }

    public function testChangePlanOnOldSubscriptionLeavesCompanyOnCurrentOne(): void
    {
        $c   = $this->company('alpha');
        $old = $this->sub($c);
        $new = $this->sub($c, ['plan_id' => 2]);

        $this->svc->changePlan($old, 3, null);

        $co = $this->row('companies', $c);
        $this->assertSame($new, (int) $co['current_subscription_id']);
        $this->assertSame(2, (int) $co['plan_id']);
    }

    // ---- all-or-nothing ----

    public function testFailureAfterSubscriptionUpdateRollsEverythingBack(): void
    {
        $c  = $this->company('alpha');
        $id = $this->sub($c);
        (new \App\Services\LicenseService())->generate($c, 1, $this->future(10), 1);
        $before = $this->row('subscriptions', $id);
        $hist   = $this->db->table('subscription_history')->countAllResults();

        // Make the license-renewal step (the last step of extend) blow up.
        $this->db->query('RENAME TABLE licenses TO licenses_hidden');

        try {
            $this->svc->extend($id, $this->future(90));
            $this->fail('Expected a database failure');
        } catch (\Throwable $e) {
            // expected
        } finally {
            $this->db->query('RENAME TABLE licenses_hidden TO licenses');
        }

        $this->assertSame($before['expires_at'], $this->row('subscriptions', $id)['expires_at']);
        $this->assertSame($hist, $this->db->table('subscription_history')->countAllResults());
    }
}
