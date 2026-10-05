<?php

namespace App\Services;

use App\Models\AuditLogModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Central place every controller/service calls to write an audit trail row.
 * Never pass a password or database credential in $old/$new — the caller
 * is responsible for stripping sensitive fields before calling log().
 */
class AuditService
{
    public function log(
        string $action,
        string $module,
        ?string $recordType = null,
        ?int $recordId = null,
        ?array $old = null,
        ?array $new = null,
        ?int $companyId = null
    ): void {
        // getUserAgent() only exists on IncomingRequest, not the CLIRequest a spark
        // command gets from service('request') — audit calls from scheduled commands
        // (e.g. subscription expiry sync) would otherwise fatal on every run.
        $request = service('request');
        $isWeb   = $request instanceof IncomingRequest;

        (new AuditLogModel())->insert([
            'platform_user_id' => session('platform_user_id'),
            'company_id'       => $companyId,
            'action'           => $action,
            'module'           => $module,
            'record_type'      => $recordType,
            'record_id'        => $recordId,
            'old_values'       => $old !== null ? json_encode($old) : null,
            'new_values'       => $new !== null ? json_encode($new) : null,
            'ip_address'       => $isWeb ? $request->getIPAddress() : 'cli',
            'user_agent'       => $isWeb ? (string) $request->getUserAgent() : 'cli',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);
    }
}
