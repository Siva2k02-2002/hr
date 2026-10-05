<?php

namespace App\Commands;

use App\Services\LicenseVerificationService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Ops tool, meant to run periodically via cron/Task Scheduler (Phase 19):
 * proactively re-verifies and re-caches every provisioned company's license
 * while the platform DB is reachable, so the offline cache
 * (LicenseVerificationService) is always warm before it's ever actually
 * needed — a platform DB outage right after a long-idle cache would
 * otherwise mean the very first company hitting that gap has nothing to
 * fall back on.
 */
class LicensesRefreshCache extends BaseCommand
{
    protected $group       = 'License';
    protected $name        = 'licenses:refresh-cache';
    protected $description = 'Re-verifies and refreshes the offline license cache for every provisioned company.';

    public function run(array $params)
    {
        $platform = db_connect('master');
        $rows     = $platform->table('companies c')
            ->select('c.id, c.code')
            ->join('company_domains cd', 'cd.company_id = c.id AND cd.is_primary = 1')
            ->where('c.provisioning_status', 'ready')
            ->where('c.deleted_at', null)
            ->get()->getResultArray();

        if ($rows === []) {
            CLI::write('No provisioned companies found.', 'yellow');

            return;
        }

        $service = new LicenseVerificationService();
        $refreshed = 0;

        foreach ($rows as $row) {
            $domain = $platform->table('company_domains')->where('company_id', $row['id'])->where('is_primary', 1)->get()->getRowArray();
            if (! $domain) {
                continue;
            }

            $result = $service->verify((int) $row['id'], $row['code'], $domain['domain']);
            CLI::write("{$row['code']}: {$result['state']}", $result['state'] === 'valid' ? 'green' : 'yellow');
            $refreshed++;
        }

        CLI::write("Done. Refreshed {$refreshed}/" . count($rows) . ' companies.', 'green');
    }
}
