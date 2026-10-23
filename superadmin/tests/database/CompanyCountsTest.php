<?php

use App\Models\CompanyModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Dashboard counts and the Companies list status filter, against the DISPOSABLE
 * database zz_sa_test (aborts unless connected to it). A company's state comes from its
 * CURRENT subscription (companies.current_subscription_id), in date, never from
 * companies.status.
 *
 * @internal
 */
final class CompanyCountsTest extends CIUnitTestCase
{
    private const DB    = 'zz_sa_test';
    private const TODAY = '2026-10-23';

    protected $db;

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
        $this->assertSame(self::DB, $this->db->query('SELECT DATABASE() AS d')->getRow()->d, 'Refusing to run: not the disposable test database.');

        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach (['subscription_history', 'subscriptions', 'company_domains', 'companies'] as $t) {
            $this->db->query("TRUNCATE TABLE `{$t}`");
        }
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    /** Insert a company; $sub = [status, starts, expires] for its current subscription, or null for none. */
    private function company(string $code, ?array $sub, string $companyStatus = 'active', bool $deleted = false): int
    {
        $this->db->table('companies')->insert([
            'code' => $code, 'name' => $code, 'status' => $companyStatus, 'provisioning_status' => 'ready', 'plan_id' => 1,
            'deleted_at' => $deleted ? '2026-10-01 00:00:00' : null,
        ]);
        $id = (int) $this->db->insertID();

        if ($sub !== null) {
            $this->addSub($id, $sub, true);
        }

        return $id;
    }

    private function addSub(int $companyId, array $sub, bool $current): int
    {
        $this->db->table('subscriptions')->insert([
            'company_id' => $companyId, 'plan_id' => 1, 'status' => $sub[0], 'starts_at' => $sub[1], 'expires_at' => $sub[2],
        ]);
        $sid = (int) $this->db->insertID();
        if ($current) {
            $this->db->table('companies')->where('id', $companyId)->update(['current_subscription_id' => $sid]);
        }

        return $sid;
    }

    private function counts(): array
    {
        return (new CompanyModel())->counts(self::TODAY);
    }

    public function testActiveInDateCountsAsActive(): void
    {
        $this->company('a', ['active', '2026-01-01', '2027-01-01']);

        $c = $this->counts();
        $this->assertSame(1, $c['active']);
        $this->assertSame(0, $c['trial']);
    }

    public function testTrialInDateCountsAsTrial(): void
    {
        $this->company('t', ['trial', '2026-10-01', '2026-11-01']);

        $this->assertSame(1, $this->counts()['trial']);
    }

    public function testExpiryTodayIsStillValid(): void
    {
        $this->company('a', ['active', '2026-01-01', self::TODAY]);
        $this->company('t', ['trial', '2026-01-01', self::TODAY]);

        $c = $this->counts();
        $this->assertSame(1, $c['active']);
        $this->assertSame(1, $c['trial']);
    }

    public function testExpiredYesterdayIsExpiredNotActiveOrTrial(): void
    {
        $this->company('a', ['active', '2026-01-01', '2026-10-22']);
        $this->company('t', ['trial', '2026-01-01', '2026-10-22']);

        $c = $this->counts();
        $this->assertSame(0, $c['active']);
        $this->assertSame(0, $c['trial']);
        $this->assertSame(2, $c['expired']);
    }

    public function testStatusExpiredWithFutureDateIsExpired(): void
    {
        $this->company('e', ['expired', '2026-01-01', '2027-01-01']);

        $this->assertSame(1, $this->counts()['expired']);
    }

    public function testFutureStartIsScheduledNotActiveOrTrial(): void
    {
        $this->company('a', ['active', '2026-10-24', '2027-01-01']);
        $this->company('t', ['trial', '2026-11-01', '2027-01-01']);

        $c = $this->counts();
        $this->assertSame(0, $c['active']);
        $this->assertSame(0, $c['trial']);
        $this->assertSame(2, $c['scheduled']);
    }

    public function testStartTodayCounts(): void
    {
        $this->company('a', ['active', self::TODAY, '2027-01-01']);

        $this->assertSame(1, $this->counts()['active']);
    }

    public function testSuspendedAndCancelledAreNotActive(): void
    {
        $this->company('s', ['suspended', '2026-01-01', '2027-01-01'], 'active');
        $this->company('c', ['cancelled', '2026-01-01', '2027-01-01'], 'active');

        $c = $this->counts();
        $this->assertSame(0, $c['active']);
        $this->assertSame(1, $c['suspended']);
        $this->assertSame(1, $c['cancelled']);
    }

    public function testCompanyStatusAloneNeverMakesItActive(): void
    {
        $this->company('x', null, 'active');                                   // no subscription at all
        $this->company('y', ['trial', '2026-01-01', '2027-01-01'], 'active'); // company says active, subscription is a trial

        $c = $this->counts();
        $this->assertSame(0, $c['active']);
        $this->assertSame(1, $c['trial']);
        $this->assertSame(1, $c['none']);
    }

    public function testMissingOrForeignCurrentSubscriptionIsNone(): void
    {
        $a = $this->company('a', ['active', '2026-01-01', '2027-01-01']);
        $b = $this->company('b', null);
        $foreign = (int) $this->db->table('companies')->where('id', $a)->get()->getRow()->current_subscription_id;
        $this->db->table('companies')->where('id', $b)->update(['current_subscription_id' => $foreign]);   // points at A's subscription
        $c = $this->company('c', null);
        $this->db->table('companies')->where('id', $c)->update(['current_subscription_id' => 99999]);      // nonexistent

        $counts = $this->counts();
        $this->assertSame(1, $counts['active']);
        $this->assertSame(2, $counts['none']);
    }

    public function testOnlyCurrentSubscriptionCountsWhenHistoryExists(): void
    {
        $id = $this->company('h', ['active', '2026-10-01', '2027-01-01']);
        $this->addSub($id, ['trial', '2026-01-01', '2026-02-01'], false);
        $this->addSub($id, ['expired', '2025-01-01', '2025-06-01'], false);
        $this->addSub($id, ['active', '2025-06-01', '2025-12-31'], false);

        $c = $this->counts();
        $this->assertSame(1, $c['active']);
        $this->assertSame(0, $c['trial']);
        $this->assertSame(0, $c['expired']);
        $this->assertSame(1, array_sum($c), 'each company counted exactly once');
    }

    public function testExpiredCurrentSubscriptionWithActiveHistoryIsExpired(): void
    {
        $id = $this->company('h', ['active', '2026-01-01', '2026-09-30']);
        $this->addSub($id, ['active', '2026-10-01', '2027-01-01'], false); // newer but NOT current

        $c = $this->counts();
        $this->assertSame(0, $c['active']);
        $this->assertSame(1, $c['expired']);
    }

    public function testArchivedCompaniesAreNotCounted(): void
    {
        $this->company('live', ['active', '2026-01-01', '2027-01-01']);
        $this->company('gone', ['active', '2026-01-01', '2027-01-01'], 'active', true);

        $c = $this->counts();
        $this->assertSame(1, $c['active']);
        $this->assertSame(1, array_sum($c));
    }

    public function testListFilterUsesTheSameDefinitionAsTheCards(): void
    {
        $this->company('a1', ['active', '2026-01-01', '2027-01-01']);
        $this->company('a2', ['active', '2026-01-01', '2026-10-22']);   // expired
        $this->company('t1', ['trial', '2026-10-01', '2026-11-01']);
        $this->company('n1', null);

        $counts = $this->counts();
        foreach (['active', 'trial', 'expired', 'none'] as $status) {
            $model = new CompanyModel();
            $model->withPlan();
            $model->withCurrentSubscription();
            $model->whereEffectiveStatus($status, self::TODAY);

            $this->assertCount($counts[$status], $model->findAll(), "filter '{$status}' must list exactly what the card counts");
        }
    }
}
