<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Builds an ad-hoc connection to one tenant's database: the health check on
 * the Companies -> Database tab, and forCompany() for the per-company
 * Company Settings screen (the only tenant data Super Admin writes — see
 * TenantSettingsService). superadmin never reads tenant business data.
 *
 * Deliberately duplicated (not shared) with the equivalent class in the
 * hrms project: the two apps are independently deployable and do not
 * share a codebase or filesystem in production.
 */
class TenantConnectionFactory
{
    public function build(string $host, int $port, string $database, string $username, string $password): BaseConnection
    {
        return \Config\Database::connect([
            'DSN'      => '',
            'hostname' => $host,
            'port'     => $port,
            'username' => $username,
            'password' => $password,
            'database' => $database,
            'DBDriver' => 'MySQLi',
            'DBPrefix' => '',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
            'strictOn' => false,
            'failover' => [],
            'encrypt'  => false,
            'compress' => false,
        ], false);
    }

    /**
     * Connection to ONE company's tenant database, resolved strictly from that
     * company's own platform records using the stored (encrypted) credentials.
     * Refuses unless the company is ready and its connection is provisioned,
     * and confirms the live connection is really on the configured database
     * before returning it. Messages are safe to show — no credentials.
     *
     * @throws \RuntimeException
     */
    public function forCompany(int $companyId): BaseConnection
    {
        $company = (new \App\Models\CompanyModel())->find($companyId);
        if (! $company) {
            throw new \RuntimeException('Company not found.');
        }
        if ($company['provisioning_status'] !== 'ready') {
            throw new \RuntimeException('This company\'s workspace is not ready yet.');
        }

        $conn = (new \App\Models\CompanyDatabaseConnectionModel())->forCompany($companyId);
        if (! $conn || $conn['status'] !== 'provisioned' || (int) $conn['company_id'] !== $companyId) {
            throw new \RuntimeException('This company has no provisioned tenant database connection.');
        }

        try {
            $password = service('encrypter')->decrypt(base64_decode((string) $conn['db_password_enc']));
        } catch (\Throwable) {
            throw new \RuntimeException('The stored database credentials could not be read.');
        }

        $db = $this->build($conn['db_host'], (int) $conn['db_port'], $conn['db_name'], $conn['db_username'], $password);

        try {
            $actual = (string) $db->query('SELECT DATABASE() AS db')->getRow()->db;
        } catch (\Throwable) {
            throw new \RuntimeException('Could not connect to the company\'s database.');
        }

        // Defence in depth: never act on a connection that is not exactly the configured tenant database.
        if (strcasecmp($actual, (string) $conn['db_name']) !== 0) {
            $db->close();

            throw new \RuntimeException('Connected database does not match the company\'s configured database. Aborted.');
        }

        return $db;
    }

    public function healthCheck(BaseConnection $db): bool
    {
        try {
            $db->query('SELECT 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
