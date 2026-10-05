<?php

namespace App\Services;

use App\Database\Seeds\TenantRbacSeeder;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\MigrationRunner;
use CodeIgniter\Database\Seeder;
use Config\Database as DatabaseConfig;
use Config\Migrations as MigrationsConfig;
use RuntimeException;

/**
 * Operator-run CLI helpers for an existing tenant database: apply migrations
 * (tenants:migrate, demo seeders) and (re)seed RBAC (rbac:resync). Both work
 * on an explicit connection handed in by the command.
 *
 * Not used by Super Admin and never run during company provisioning: the
 * tenant database, its schema and its RBAC data are imported manually.
 */
class TenantProvisioningService
{
    public function runMigrations(BaseConnection $db): void
    {
        $runner = new MigrationRunner(config(MigrationsConfig::class), $db);
        $runner->setNamespace('App');

        if (! $runner->latest()) {
            throw new RuntimeException('Tenant schema migration failed.');
        }
    }

    public function seedRbac(BaseConnection $db): void
    {
        $seeder = new Seeder(config(DatabaseConfig::class), $db);
        $seeder->call(TenantRbacSeeder::class);
    }
}
