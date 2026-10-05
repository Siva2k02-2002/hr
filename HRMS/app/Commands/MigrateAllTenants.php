<?php

namespace App\Commands;

use App\Services\TenantConnectionFactory;
use App\Services\TenantProvisioningService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * Ops tool: applies any migrations added to this codebase since a tenant was
 * provisioned. Tenants are imported manually from the complete HRMS SQL; this
 * command brings any tenant database up to date with newer migrations
 * (e.g. password_history). Safe to re-run — CI4's
 * migration runner tracks what each tenant database has already applied.
 */
class MigrateAllTenants extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'tenants:migrate';
    protected $description = 'Runs pending migrations against every provisioned tenant database.';
    protected $usage       = 'tenants:migrate [--code <company_code>]';
    protected $options     = ['--code' => 'Limit to a single company code instead of every provisioned tenant'];

    public function run(array $params)
    {
        $platform = db_connect('master');
        $builder  = $platform->table('companies c')
            ->select('c.id as company_id, c.code, cdc.db_host, cdc.db_port, cdc.db_name, cdc.db_username, cdc.db_password_enc')
            ->join('company_database_connections cdc', 'cdc.company_id = c.id')
            ->where('cdc.status', 'provisioned')
            ->where('c.deleted_at', null);

        $onlyCode = CLI::getOption('code');
        if ($onlyCode) {
            $builder->where('c.code', $onlyCode);
        }

        $rows = $builder->get()->getResultArray();

        if ($rows === []) {
            CLI::error('No provisioned tenants found.');

            return;
        }

        $provisioning = new TenantProvisioningService();
        $factory      = new TenantConnectionFactory();
        $failures     = 0;

        foreach ($rows as $row) {
            CLI::write("Migrating {$row['code']} ({$row['db_name']})...", 'yellow');

            try {
                $password = service('encrypter')->decrypt(base64_decode((string) $row['db_password_enc']));
                $db       = $factory->build($row['db_host'], (int) $row['db_port'], $row['db_name'], $row['db_username'], $password);

                if (! $factory->healthCheck($db)) {
                    throw new \RuntimeException('database unreachable');
                }

                $provisioning->runMigrations($db);
                CLI::write("  OK: {$row['code']}", 'green');
            } catch (Throwable $e) {
                $failures++;
                CLI::error("  FAILED: {$row['code']} — {$e->getMessage()}");
            }
        }

        CLI::write('Done. ' . (count($rows) - $failures) . '/' . count($rows) . ' tenants migrated.', $failures > 0 ? 'red' : 'green');
    }
}
