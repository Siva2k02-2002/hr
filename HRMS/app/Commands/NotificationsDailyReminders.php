<?php

namespace App\Commands;

use App\Models\EmployeeModel;
use App\Services\NotificationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Ops tool, meant to run once daily via cron/Task Scheduler (Phase 19):
 * notifies HR (everyone holding employee.edit) about today's birthdays and
 * work anniversaries. Company-wide totals for the next 30 days already show
 * on the dashboard (DashboardService) — this is the day-of push on top of
 * that, per Phase 13's "Birthday Reminder"/"Work Anniversary" events.
 *
 * Iterates every provisioned tenant the same way tenants:migrate does, since
 * this runs outside any single tenant's HTTP request.
 */
class NotificationsDailyReminders extends BaseCommand
{
    protected $group       = 'Notifications';
    protected $name        = 'notifications:daily-reminders';
    protected $description = "Notifies HR of today's employee birthdays and work anniversaries, for every tenant.";

    public function run(array $params)
    {
        $platform = db_connect('master');
        $companies = $platform->table('companies c')
            ->select('c.id as company_id, c.code, cdc.db_host, cdc.db_port, cdc.db_name, cdc.db_username, cdc.db_password_enc')
            ->join('company_database_connections cdc', 'cdc.company_id = c.id')
            ->where('cdc.status', 'provisioned')
            ->where('c.deleted_at', null)
            ->get()->getResultArray();

        $factory = new \App\Services\TenantConnectionFactory();
        $total   = 0;

        foreach ($companies as $company) {
            try {
                $password = service('encrypter')->decrypt(base64_decode((string) $company['db_password_enc']));
                $db       = $factory->build($company['db_host'], (int) $company['db_port'], $company['db_name'], $company['db_username'], $password);
                if (! $factory->healthCheck($db)) {
                    continue;
                }

                $sent = $this->remindForTenant($db);
                $total += $sent;
                CLI::write("{$company['code']}: {$sent} reminder(s).", $sent > 0 ? 'green' : 'yellow');
            } catch (\Throwable $e) {
                CLI::error("{$company['code']}: {$e->getMessage()}");
            }
        }

        CLI::write("Done. {$total} reminder(s) sent across " . count($companies) . ' tenant(s).', 'green');
    }

    private function remindForTenant($db): int
    {
        $employees = (new EmployeeModel($db))
            ->select('id, first_name, last_name, date_of_birth, date_of_joining')
            ->where('status', 'active')
            ->groupStart()->where('date_of_birth IS NOT NULL')->orWhere('date_of_joining IS NOT NULL')->groupEnd()
            ->findAll();

        $notifications = new NotificationService($db);
        $hrUserIds     = $notifications->usersWithPermission('employee.edit');
        $today          = date('m-d');
        $sent           = 0;

        foreach ($employees as $e) {
            $name = trim($e['first_name'] . ' ' . $e['last_name']);

            if ($e['date_of_birth'] && date('m-d', strtotime($e['date_of_birth'])) === $today) {
                foreach ($hrUserIds as $userId) {
                    $notifications->birthdayReminder($userId, $name, date('Y-m-d'));
                    $sent++;
                }
            }

            if ($e['date_of_joining'] && date('m-d', strtotime($e['date_of_joining'])) === $today) {
                $years = (int) date('Y') - (int) date('Y', strtotime($e['date_of_joining']));
                if ($years > 0) {
                    foreach ($hrUserIds as $userId) {
                        $notifications->workAnniversary($userId, $name, $years, date('Y-m-d'));
                        $sent++;
                    }
                }
            }
        }

        return $sent;
    }
}
