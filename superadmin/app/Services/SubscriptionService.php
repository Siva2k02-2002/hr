<?php

namespace App\Services;

use App\Models\CompanyModel;
use App\Models\PlanModel;
use App\Models\SubscriptionHistoryModel;
use App\Models\SubscriptionModel;

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

    public function create(array $data): int
    {
        $this->subscriptions->insert($data);
        $subscriptionId = $this->subscriptions->getInsertID();

        $this->recordHistory($subscriptionId, 'created', null, $data);
        $this->syncCompanyIfCurrent($data['company_id'], $subscriptionId, forceCurrent: true);
        $this->audit->log('create', 'subscription', 'subscription', $subscriptionId, null, $data, $data['company_id']);

        return $subscriptionId;
    }

    public function changeStatus(int $subscriptionId, string $status, ?string $remarks = null): void
    {
        $old = $this->subscriptions->find($subscriptionId);
        $update = ['status' => $status];
        if ($remarks !== null) {
            $update['remarks'] = $remarks;
        }
        $this->subscriptions->update($subscriptionId, $update);

        $this->recordHistory($subscriptionId, 'status_change', ['status' => $old['status']], $update);
        $this->syncCompanyIfCurrent($old['company_id'], $subscriptionId);
        $this->audit->log('status_change', 'subscription', 'subscription', $subscriptionId, $old, $update, $old['company_id']);
    }

    public function extend(int $subscriptionId, string $newExpiresAt): void
    {
        $old = $this->subscriptions->find($subscriptionId);
        $status = in_array($old['status'], ['expired', 'expiring', 'suspended'], true) ? 'active' : $old['status'];

        $this->subscriptions->update($subscriptionId, ['expires_at' => $newExpiresAt, 'status' => $status]);

        $this->recordHistory($subscriptionId, 'extended', ['expires_at' => $old['expires_at']], ['expires_at' => $newExpiresAt]);
        $this->syncCompanyIfCurrent($old['company_id'], $subscriptionId);
        $this->audit->log('extend', 'subscription', 'subscription', $subscriptionId, $old, ['expires_at' => $newExpiresAt], $old['company_id']);
    }

    public function changePlan(int $subscriptionId, int $newPlanId, ?int $employeeLimitOverride): void
    {
        $old = $this->subscriptions->find($subscriptionId);
        $update = ['plan_id' => $newPlanId, 'employee_limit_override' => $employeeLimitOverride];
        $this->subscriptions->update($subscriptionId, $update);

        $this->recordHistory($subscriptionId, 'plan_changed', ['plan_id' => $old['plan_id']], $update);
        $this->syncCompanyIfCurrent($old['company_id'], $subscriptionId);
        $this->audit->log('plan_change', 'subscription', 'subscription', $subscriptionId, $old, $update, $old['company_id']);
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

    private function syncCompanyIfCurrent(int $companyId, int $subscriptionId, bool $forceCurrent = false): void
    {
        $company = $this->companies->find($companyId);

        if (! $forceCurrent && (int) ($company['current_subscription_id'] ?? 0) !== $subscriptionId) {
            return;
        }

        $subscription = $this->subscriptions->find($subscriptionId);
        $plan         = $this->plans->find($subscription['plan_id']);

        $this->companies->update($companyId, [
            'current_subscription_id' => $subscriptionId,
            'status'                  => $subscription['status'],
            'plan_id'                 => $subscription['plan_id'],
            'employee_limit'          => $subscription['employee_limit_override'] ?? $plan['employee_limit'],
        ]);
    }
}
