<?php

use App\Filters\TenantResolver;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TenantSubscriptionGateTest extends TestCase
{
    private function row(array $o = []): array
    {
        return array_merge(['company_id' => 5, 'sub_company_id' => 5, 'sub_status' => 'trial', 'expires_at' => '2026-10-31'], $o);
    }

    private function reason(array $o, string $today = '2026-10-23'): ?string
    {
        return TenantResolver::subscriptionDenial($this->row($o), $today)['reason'] ?? null;
    }

    public function testActiveUnexpiredAllowed(): void { $this->assertNull($this->reason(['sub_status' => 'active'])); }
    public function testUnexpiredTrialAllowed(): void { $this->assertNull($this->reason([])); }
    public function testExpiredTrialDenied(): void { $this->assertSame('expired', $this->reason([], '2026-11-01')); }
    public function testExpiredActiveDenied(): void { $this->assertSame('expired', $this->reason(['sub_status' => 'active'], '2026-11-01')); }
    public function testExpiredStatusDeniedEvenWithFutureDate(): void { $this->assertSame('expired', $this->reason(['sub_status' => 'expired'])); }
    public function testSuspendedDenied(): void { $this->assertSame('status_suspended', $this->reason(['sub_status' => 'suspended'])); }
    public function testCancelledDenied(): void { $this->assertSame('status_cancelled', $this->reason(['sub_status' => 'cancelled'])); }
    public function testMissingCurrentSubscriptionIdDenied(): void { $this->assertSame('no_subscription', $this->reason(['sub_status' => null, 'sub_company_id' => null, 'expires_at' => null])); }
    public function testNonexistentSubscriptionRecordDenied(): void { $this->assertSame('no_subscription', $this->reason(['sub_status' => null, 'sub_company_id' => null, 'expires_at' => null])); }
    public function testOtherCompanysSubscriptionDenied(): void { $this->assertSame('subscription_company_mismatch', $this->reason(['sub_company_id' => 6])); }
    public function testExpiryDayItselfStillValid(): void { $this->assertNull($this->reason([], '2026-10-31')); }
    public function testDayAfterExpiryDenied(): void { $this->assertSame('expired', $this->reason([], '2026-11-01')); }
    public function testPlanMismatchIrrelevant(): void { $this->assertNull($this->reason(['company_plan_id' => 1, 'sub_plan_id' => 2])); }
}
