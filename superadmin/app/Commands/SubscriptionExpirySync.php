<?php

namespace App\Commands;

use App\Models\SubscriptionModel;
use App\Services\SubscriptionService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Ops tool, meant to run daily via cron/Task Scheduler (Phase 19 deployment):
 *
 *  1. Flips any 'active'/'trial' subscription whose expires_at has already
 *     passed to 'expired' — nothing currently does this automatically, so an
 *     expired subscription's status column stays stale until an admin
 *     manually intervenes. This is data hygiene only: HRMS's own
 *     TenantResolver already independently checks expires_at on every tenant
 *     request regardless of this status column, so access control was never
 *     dependent on it — this just keeps list screens/filters accurate.
 *  2. Emails the company's contact_email once per subscription when it's
 *     within the reminder window, tracked via last_reminder_sent_at so a
 *     daily run doesn't re-send the same notice every day.
 */
class SubscriptionExpirySync extends BaseCommand
{
    protected $group       = 'Subscriptions';
    protected $name        = 'subscriptions:expiry-sync';
    protected $description = 'Marks expired subscriptions and emails renewal reminders for ones expiring soon.';
    protected $usage       = 'subscriptions:expiry-sync [--reminder-days 14]';
    protected $options     = ['--reminder-days' => 'How many days before expiry to start reminding (default 14)'];

    public function run(array $params)
    {
        $reminderDays = (int) (CLI::getOption('reminder-days') ?? 14);
        $subscriptions = new SubscriptionModel();
        $service       = new SubscriptionService();

        $expired = $subscriptions->whereIn('status', ['active', 'trial', 'expiring'])
            ->where('expires_at <', date('Y-m-d'))
            ->findAll();

        foreach ($expired as $sub) {
            $service->changeStatus((int) $sub['id'], 'expired', 'Automatically expired by subscriptions:expiry-sync.');
            CLI::write("Expired subscription #{$sub['id']} (company #{$sub['company_id']}).", 'yellow');
        }

        $expiringSoon = $subscriptions->withCompanyAndPlan()
            ->whereIn('subscriptions.status', ['active', 'trial', 'expiring'])
            ->where('subscriptions.expires_at >=', date('Y-m-d'))
            ->where('subscriptions.expires_at <=', date('Y-m-d', strtotime("+{$reminderDays} days")))
            ->groupStart()
                ->where('subscriptions.last_reminder_sent_at IS NULL')
                ->orWhere('subscriptions.last_reminder_sent_at <', date('Y-m-d', strtotime('-1 day')))
            ->groupEnd()
            ->findAll();

        $sent = 0;
        foreach ($expiringSoon as $sub) {
            if (empty($sub['company_id'])) {
                continue;
            }

            $contactEmail = $this->contactEmailFor((int) $sub['company_id']);
            if (! $contactEmail) {
                CLI::error("Subscription #{$sub['id']}: company has no contact_email — skipping reminder.");
                continue;
            }

            $daysLeft = (int) ceil((strtotime($sub['expires_at']) - strtotime('today')) / DAY);

            $email = service('email');
            $email->setTo($contactEmail);
            $email->setSubject('Your ' . $sub['company_name'] . ' subscription expires soon');
            $email->setMessage(view('emails/subscription_expiring', [
                'companyName' => $sub['company_name'],
                'planName'    => $sub['plan_name'],
                'expiresAt'   => $sub['expires_at'],
                'daysLeft'    => $daysLeft,
            ]));

            if ($email->send()) {
                $subscriptions->update((int) $sub['id'], ['last_reminder_sent_at' => date('Y-m-d H:i:s')]);
                $sent++;
                CLI::write("Reminder sent for subscription #{$sub['id']} ({$sub['company_name']}, {$daysLeft}d left).", 'green');
            } else {
                CLI::error("Reminder failed for subscription #{$sub['id']}: " . $email->printDebugger(['headers']));
            }
        }

        CLI::write('Done. ' . count($expired) . ' expired, ' . $sent . '/' . count($expiringSoon) . ' reminders sent.', 'green');
    }

    private function contactEmailFor(int $companyId): ?string
    {
        $company = (new \App\Models\CompanyModel())->find($companyId);

        return $company['contact_email'] ?? $company['primary_admin_email'] ?? null;
    }
}
