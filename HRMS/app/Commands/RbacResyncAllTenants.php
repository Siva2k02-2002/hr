<?php

namespace App\Commands;

use App\Models\CompanySettingModel;
use App\Services\TenantConnectionFactory;
use App\Services\TenantProvisioningService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * Ops tool: re-runs TenantRbacSeeder against every already-provisioned tenant
 * database and bumps its permissions_version, so a new/changed permission
 * slug (e.g. TenantPermissions.php catalog edits) reaches tenants that were
 * provisioned before the change. Same shape as MigrateAllTenants. Safe to
 * re-run — TenantRbacSeeder is idempotent and never deletes a slug/grant
 * outside what ROLE_PERMISSIONS currently declares for system roles.
 */
class RbacResyncAllTenants extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'tenants:resync-rbac';
    protected $description = 'Re-seeds the RBAC permission catalog + system role grants against every provisioned tenant database.';
    protected $usage       = 'tenants:resync-rbac [--code <company_code>]';
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
            CLI::write("Re-seeding RBAC for {$row['code']} ({$row['db_name']})...", 'yellow');

            try {
                $password = service('encrypter')->decrypt(base64_decode((string) $row['db_password_enc']));
                $db       = $factory->build($row['db_host'], (int) $row['db_port'], $row['db_name'], $row['db_username'], $password);

                if (! $factory->healthCheck($db)) {
                    throw new \RuntimeException('database unreachable');
                }

                $provisioning->seedRbac($db);
                (new CompanySettingModel($db))->bumpPermissionsVersion();
                CLI::write("  OK: {$row['code']}", 'green');
            } catch (Throwable $e) {
                $failures++;
                CLI::error("  FAILED: {$row['code']} — {$e->getMessage()}");
            }
        }

        CLI::write('Done. ' . (count($rows) - $failures) . '/' . count($rows) . ' tenants resynced.', $failures > 0 ? 'red' : 'green');
    }
}
