<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database as DatabaseConfig;

/**
 * Builds a real, ad-hoc database connection for one tenant, from connection
 * details already resolved by TenantResolver. This is the ONLY place tenant
 * connections are constructed at request time — nothing hard-codes a
 * tenant's database name anywhere else in the codebase.
 */
class TenantConnectionFactory
{
    public function build(string $host, int $port, string $database, string $username, string $password): BaseConnection
    {
        return \Config\Database::connect([
            'DSN'          => '',
            'hostname'     => $host,
            'port'         => $port,
            'username'     => $username,
            'password'     => $password,
            'database'     => $database,
            'DBDriver'     => 'MySQLi',
            'DBPrefix'     => '',
            'pConnect'     => false,
            'DBDebug'      => true,
            'charset'      => 'utf8mb4',
            'DBCollat'     => 'utf8mb4_general_ci',
            'strictOn'     => false,
            'failover'     => [],
            'encrypt'      => false,
            'compress'     => false,
        ], false);
    }

    /** True if a live connection + trivial query succeed. Never throws. */
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
