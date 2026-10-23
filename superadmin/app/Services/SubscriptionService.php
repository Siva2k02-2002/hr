<?php

namespace App\Services;

use App\Models\CompanyModel;
use App\Models\LicenseModel;
use App\Models\PlanModel;
use App\Models\SubscriptionHistoryModel;
use App\Models\SubscriptionModel;
use RuntimeException;
use Throwable;

/**
 * Every state change here appends a subscription_history row alongside the
 * update — nothing on the subscriptions table mutates silently. Whenever the
 * subscription being changed is the company's *current* one, the company's
 * denormalized status/plan/employee_limit columns are kept in sync so list
 * screens can filter without joining subscription history every time.
 */
class SubscriptionService
{
    public function __construct(
        private SubscriptionModel $subscriptions = new SubscriptionModel(),
        private SubscriptionHistoryModel $history = new SubscriptionHistoryModel(),
        private CompanyModel $companies = new CompanyModel(),
        private PlanModel $plans = new PlanModel(),
        private AuditService $audit = new AuditService(),
    ) {
    }

    /** Returns an error message for an invalid start/expiry pair, or null when the dates are usable. */
    public static function dateRangeError(string $startsAt, string $expiresAt): ?string
    {
        $start = strtotime($startsAt);
        $end   = strtotime($expiresAt);
        if ($start === false || $end === false) {
            return 'Start and expiry dates must be valid dates.';
        }
        if ($end < $start) {
            return 'Expiry date cannot be before the start date.';
        }

        return null;
    }

    public function create(array $data): int
    {
        if (! $this->companies->find((int) $data['company_id'])) {
            throw new RuntimeException('Company not found.');
        }
        if (! $this->plans->find((int) $data['plan_id'])) {
            throw new RuntimeException('Plan not found.');
        }
        if ($error = self::dateRangeError((string) $data['starts_at'], (string) $data['expires_at'])) {
            throw new RuntimeException($error);
        }

        return $this->atomic(function () use ($data) {
            $this->subscriptions->insert($data);
            $subscriptionId = $this->subscriptions->getInsertID();

            $this->recordHistory($subscriptionId, 'created', null, $data);
            $this->syncCompanyIfCurrent($data['company_id'], $subscriptionId, forceCurrent: true);
            $this->audit->log('create', 'subscription', 'subscription', $subscriptionId, null, $data, $data['company_id']);

            return $subscriptionId;
        });
    }

    public function changeStatus(int $subscriptionId, string $status, ?string $remarks = null): void
    {
        $old = $this->findOrFail($subscriptionId);
        $update = ['status' => $status];
        if ($remarks !== null) {
            $update['remarks'] = $remarks;
        }

        $this->atomic(function () use ($subscriptionId, $old, $update) {
            $this->subscriptions->update($subscriptionId, $update);

            $this->recordHistory($subscriptionId, 'status_change', ['status' => $old['status']], $update);
            $this->syncCompanyIfCurrent($old['company_id'], $subscriptionId);
            $this->audit->log('status_change', 'subscription', 'subscription', $subscriptionId, $old, $update, $old['company_id']);
        });
    }

    public function extend(int $subscriptionId, string $newExpiresAt): void
    {
        $old = $this->findOrFail($subscriptionId);

        $ts = strtotime($newExpiresAt);
        if ($ts === false) {
            throw new RuntimeException('The new expiry date is not a valid date.');
        }
        if (date('Y-m-d', $ts) < date('Y-m-d')) {
            throw new RuntimeException('The new expiry date cannot be in the past.');
        }
        if (date('Y-m-d', $ts) < $old['starts_at']) {
            throw new RuntimeException('The new expiry date cannot be before the subscription start date.');
        }

        $status = in_array($old['status'], ['expired', 'expiring', 'suspended'], true) ? 'active' : $old['status'];

        $this->atomic(function () use ($subscriptionId, $old, $newExpiresAt, $status) {
            $this->subscriptions->update($subscriptionId, ['expires_at' => $newExpiresAt, 'status' => $status]);

            $this->recordHistory($subscriptionId, 'extended', ['expires_at' => $old['expires_at']], ['expires_at' => $newExpiresAt]);
            $this->syncCompanyIfCurrent($old['company_id'], $subscriptionId);
            $this->syncLicenseExpiry((int) $old['company_id'], $subscriptionId, $newExpiresAt);
            $this->audit->log('extend', 'subscription', 'subscription', $subscriptionId, $old, ['expires_at' => $newExpiresAt], $old['company_id']);
        });
    }

    public function changePlan(int $subscriptionId, int $newPlanId, ?int $employeeLimitOverride): void
    {
        $old = $this->findOrFail($subscriptionId);
        if (! $this->plans->find($newPlanId)) {
            throw new RuntimeException('Plan not found.');
        }
        if ($employeeLimitOverride !== null && $employeeLimitOverride < 1) {
            throw new RuntimeException('Employee limit override must be at least 1.');
        }
        $update = ['plan_id' => $newPlanId, 'employee_limit_override' => $employeeLimitOverride];

        $this->atomic(function () use ($subscriptionId, $old, $update) {
            $this->subscriptions->update($subscriptionId, $update);

            $this->recordHistory($subscriptionId, 'plan_changed', ['plan_id' => $old['plan_id']], $update);
            $this->syncCompanyIfCurrent($old['company_id'], $subscriptionId);
            $this->audit->log('plan_change', 'subscription', 'subscription', $subscriptionId, $old, $update, $old['company_id']);
        });
    }

    private function findOrFail(int $subscriptionId): array
    {
        $subscription = $this->subscriptions->find($subscriptionId);
        if (! $subscription) {
            throw new RuntimeException('Subscription not found.');
        }

        return $subscription;
    }

    /** Runs the subscription write, its history row, the company sync and any license renewal as one unit - all or nothing. */
    private function atomic(callable $work)
    {
        $db = db_connect();
        $db->transBegin();

        try {
            $result = $work();
            $db->transCommit();

            return $result;
        } catch (Throwable $e) {
            $db->transRollback();
            // CI4 keeps the connection flagged as failed after a failed query; clear it so later
            // transactions in the same process (e.g. the expiry-sync loop) are not silently rolled back.
            $db->resetTransStatus();

            throw $e;
        }
    }

    private function recordHistory(int $subscriptionId, string $action, ?array $old, ?array $new): void
    {
        $this->history->insert([
            'subscription_id' => $subscriptionId,
            'action'          => $action,
            'old_values'      => $old !== null ? json_encode($old) : null,
            'new_values'      => $new !== null ? json_encode($new) : null,
            'performed_by'    => session('platform_user_id'),
            'performed_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /** Keep the company's license expiry equal to the current subscription's, so the company page doesn't show a stale date. */
    private function syncLicenseExpiry(int $companyId, int $subscriptionId, string $newExpiresAt): void
    {
        $company = $this->companies->find($companyId);
        if ((int) ($company['current_subscription_id'] ?? 0) !== $subscriptionId) {
            return;
        }

        $current = (new LicenseModel())->currentFor($companyId);
        if (! $current || $current['status'] === 'revoked') {
            return;
        }

        (new LicenseService())->renew($companyId, $newExpiresAt, (int) session('platform_user_id'));
    }

    private function syncCompanyIfCurrent(int $companyId, int $subscriptionId, bool $forceCurrent = false): void
    {
        $company = $this->companies->find($companyId);

        if (! $forceCurrent && (int) ($company['current_subscription_id'] ?? 0) !== $subscriptionId) {
            return;
        }

        $subscription = $this->subscriptions->find($subscriptionId);
        $plan         = $this->plans->find($subscription['plan_id']);

        // A company the platform admin suspended or cancelled stays that way: renewing or
        // re-planning a subscription must never silently lift it. Only an explicit
        // "Activate company" (CompanyService::activate) does. The one change allowed here is
        // escalating a suspended company to cancelled when its subscription is cancelled.
        $status = $subscription['status'];
        if (in_array($company['status'] ?? '', ['suspended', 'cancelled'], true)) {
            $status = $subscription['status'] === 'cancelled' ? 'cancelled' : $company['status'];
        }

        $this->companies->update($companyId, [
            'current_subscription_id' => $subscriptionId,
            'status'                  => $status,
            'plan_id'                 => $subscription['plan_id'],
            'employee_limit'          => $subscription['employee_limit_override'] ?? $plan['employee_limit'],
        ]);
    }
}
