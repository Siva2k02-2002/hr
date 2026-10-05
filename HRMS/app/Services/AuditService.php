<?php

namespace App\Services;

use App\Models\AuditLogModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Central place every tenant service calls to write an audit trail row.
 * Never pass a password or credential in $old/$new — the caller is
 * responsible for stripping sensitive fields before calling log().
 */
class AuditService
{
    /**
     * @param BaseConnection|null $conn Audit rows are tenant data. Callers that run before a controller
     *                                      (e.g. the TenantResolver filter) pass the tenant connection explicitly;
     *                                      everyone else gets the request's resolved tenant connection.
     */
    public function __construct(private ?BaseConnection $conn = null)
    {
    }

    public function log(
        string $action,
        string $module,
        ?string $recordType = null,
        ?int $recordId = null,
        ?array $old = null,
        ?array $new = null,
        ?int $employeeId = null
    ): void {
        // getUserAgent() only exists on IncomingRequest, not the CLIRequest a spark
        // command gets from service('request') — a scheduled command touching an
        // audited service (e.g. notifications:daily-reminders) would otherwise fatal.
        $request = service('request');
        $isWeb   = $request instanceof IncomingRequest;

        (new AuditLogModel($this->conn ?? service('tenantContext')->db()))->insert([
            'user_id'     => session('tenant_user_id'),
            'employee_id' => $employeeId,
            'action'      => $action,
            'module'      => $module,
            'record_type' => $recordType,
            'record_id'   => $recordId,
            'old_values'  => $old !== null ? json_encode($old) : null,
            'new_values'  => $new !== null ? json_encode($new) : null,
            'ip_address'  => $isWeb ? $request->getIPAddress() : 'cli',
            'user_agent'  => $isWeb ? (string) $request->getUserAgent() : 'cli',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** @param array<string,mixed> $new */
    public function logCreate(string $module, string $recordType, int $recordId, array $new, ?int $employeeId = null): void
    {
        $this->log('create', $module, $recordType, $recordId, null, $new, $employeeId);
    }

    /**
     * @param array<string,mixed> $old
     * @param array<string,mixed> $new
     */
    public function logUpdate(string $module, string $recordType, int $recordId, array $old, array $new, ?int $employeeId = null): void
    {
        $this->log('update', $module, $recordType, $recordId, $old, $new, $employeeId);
    }

    /** @param array<string,mixed> $old */
    public function logDelete(string $module, string $recordType, int $recordId, array $old, ?int $employeeId = null): void
    {
        $this->log('delete', $module, $recordType, $recordId, $old, null, $employeeId);
    }
}
