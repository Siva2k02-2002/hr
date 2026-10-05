<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Ops tool: removes offline license cache entries that are either past
 * license.offlineCacheHours or belong to a company that no longer exists /
 * is no longer provisioned. The cache's own TTL already stops a stale entry
 * from being *used* (LicenseVerificationService checks cached_at itself on
 * every read), so this is disk hygiene, not a correctness fix — safe to run
 * occasionally via cron alongside licenses:refresh-cache.
 */
class LicensesCleanupCache extends BaseCommand
{
    protected $group       = 'License';
    protected $name        = 'licenses:cleanup-cache';
    protected $description = 'Removes offline license cache entries for expired/removed companies.';

    private const CACHE_PREFIX = 'license_offline_';

    public function run(array $params)
    {
        $platform = db_connect('master');
        $validCodes = array_column(
            $platform->table('companies')->select('code')->where('deleted_at', null)->where('provisioning_status', 'ready')->get()->getResultArray(),
            'code'
        );

        $maxAgeHours = (int) (env('license.offlineCacheHours') ?: 72);
        $cache       = cache();
        $removed     = 0;

        // The file cache handler doesn't expose "list all keys" generically across
        // drivers, so this walks companies we know about (same source list
        // licenses:refresh-cache uses) rather than scanning the cache store directly.
        $allCodes = array_column($platform->table('companies')->select('code')->get()->getResultArray(), 'code');

        foreach ($allCodes as $code) {
            $key    = self::CACHE_PREFIX . $code;
            $cached = $cache->get($key);
            if (! $cached) {
                continue;
            }

            $stale = ! in_array($code, $validCodes, true)
                || strtotime($cached['cached_at']) < strtotime("-{$maxAgeHours} hours");

            if ($stale) {
                $cache->delete($key);
                $removed++;
                CLI::write("Removed stale cache for {$code}.", 'yellow');
            }
        }

        CLI::write("Done. Removed {$removed} stale cache entr" . ($removed === 1 ? 'y' : 'ies') . '.', 'green');
    }
}
