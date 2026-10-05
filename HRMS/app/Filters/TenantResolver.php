<?php

namespace App\Filters;

use App\Services\LicenseVerificationService;
use App\Services\TenantConnectionFactory;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * Resolves tenant identity from the request hostname alone, before any
 * tenant-facing controller runs. Never trusts GET/POST/session/previous
 * requests for tenant identity — see TenantContext for the full contract.
 *
 * Flow: hostname -> company_domains -> companies -> company status check
 * -> subscription check -> decrypt tenant DB credentials -> live-connect
 * -> populate TenantContext. Any failure at any step stops the request
 * with a clean, safe message; it never falls back to a default database.
 */
class TenantResolver implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Deliberately the raw Host header, not $request->getUri()->getHost() —
        // CI4's SiteURI canonicalizes the host to app.baseURL for security,
        // which would make every tenant resolve to the same fixed host. Tenant
        // identity must come from what the client actually sent.
        $host = strtolower((string) $request->getServer('HTTP_HOST'));
        $host = explode(':', $host)[0];

        $platformDb = db_connect('master');

        $row = $platformDb->table('company_domains cd')
            ->select('
                c.id as company_id, c.code, c.name, c.status as company_status, c.provisioning_status,
                cdc.db_host, cdc.db_port, cdc.db_name, cdc.db_username, cdc.db_password_enc, cdc.status as conn_status,
                s.status as sub_status, s.expires_at
            ')
            ->join('companies c', 'c.id = cd.company_id')
            ->join('company_database_connections cdc', 'cdc.company_id = c.id', 'left')
            ->join('subscriptions s', 's.id = c.current_subscription_id', 'left')
            ->where('cd.domain', $host)
            ->where('c.deleted_at', null)
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->deny('Company not found.', 404);
        }

        if ($row['provisioning_status'] !== 'ready') {
            return $this->deny('This company workspace is still being set up. Please try again shortly.', 503);
        }

        if ($row['company_status'] === 'suspended') {
            return $this->deny('This company\'s access has been suspended. Contact your administrator.', 403);
        }

        if ($row['company_status'] === 'cancelled') {
            return $this->deny('This company account is no longer active.', 403);
        }

        if (! $row['sub_status'] || in_array($row['sub_status'], ['suspended', 'cancelled'], true)) {
            return $this->deny('This company\'s subscription is not active. Contact your administrator.', 403);
        }

        if ($row['expires_at'] !== null && $row['expires_at'] < date('Y-m-d')) {
            return $this->deny('This company\'s subscription has expired. Contact your administrator.', 403);
        }

        if (! $row['db_name'] || $row['conn_status'] !== 'provisioned') {
            return $this->deny('This company workspace is still being set up. Please try again shortly.', 503);
        }

        try {
            $password = service('encrypter')->decrypt(base64_decode((string) $row['db_password_enc']));
        } catch (Throwable $e) {
            log_message('critical', 'Tenant credential decrypt failed for company {code}: {msg}', [
                'code' => $row['code'], 'msg' => $e->getMessage(),
            ]);

            return $this->deny('Service temporarily unavailable. Please try again shortly.', 503);
        }

        $factory = new TenantConnectionFactory();
        $db      = $factory->build(
            $row['db_host'],
            (int) $row['db_port'],
            $row['db_name'],
            $row['db_username'],
            $password
        );

        if (! $factory->healthCheck($db)) {
            log_message('critical', 'Tenant DB unreachable for company {code} ({db})', [
                'code' => $row['code'], 'db' => $row['db_name'],
            ]);

            return $this->deny('Service temporarily unavailable. Please try again shortly.', 503);
        }

        service('tenantContext')->set(
            (int) $row['company_id'],
            $row['code'],
            $row['name'],
            $host,
            $row['sub_status'],
            $db
        );

        // attendance_settings.timezone was stored and editable in Settings but never
        // actually applied anywhere — every date()/strtotime() call in the app ran on
        // whatever zone the server happened to be in. Setting it here, once per request
        // right after the tenant's own DB connection is live, makes every subsequent
        // date computation this request makes (attendance "today", payroll due dates,
        // log timestamps) honor the company's configured zone instead.
        try {
            $timezone = (new \App\Models\AttendanceSettingModel($db))->current()['timezone'] ?? null;
            if ($timezone) {
                date_default_timezone_set($timezone);
            }
        } catch (Throwable) {
            // Non-fatal — request proceeds on the server's default timezone.
        }

        return $this->checkLicense((int) $row['company_id'], $row['code'], $host, $db);
    }

    /**
     * Layered on top of the company/subscription check above, not a replacement for it —
     * a company with no license row at all (every tenant provisioned before this feature
     * existed) is waved through unenforced rather than locked out; the license only starts
     * being checked once one is actually issued (new provisioning, or a renewal/revoke
     * action in Super Admin). Revoked/expired-beyond-grace/tampered/wrong-domain licenses
     * block login; an expired-but-in-grace license sets a session flag the layout reads to
     * show a warning banner instead.
     */
    private function checkLicense(int $companyId, string $companyCode, string $host, BaseConnection $conn)
    {
        $result = (new LicenseVerificationService())->verify($companyId, $companyCode, $host);

        session()->remove('license_grace_days_left');

        switch ($result['state']) {
            case 'no_license':
                return null;

            case 'valid':
                return null;

            case 'grace':
                session()->set('license_grace_days_left', $result['graceDaysLeft']);
                $this->logLicenseEvent($conn, 'license_grace', $result);

                return null;

            case 'revoked':
                $this->logLicenseEvent($conn, 'license_denied_revoked', $result);

                return $this->deny('This company\'s license has been revoked. Contact your account manager.', 403);

            case 'expired':
                $this->logLicenseEvent($conn, 'license_denied_expired', $result);

                return $this->deny('This company\'s license has expired. Contact your account manager to renew.', 403);

            case 'domain_mismatch':
                $this->logLicenseEvent($conn, 'license_denied_domain', $result);

                return $this->deny('This license is not valid for this domain.', 403);

            case 'tampered':
                log_message('critical', 'License signature mismatch for company {code} — record may have been tampered with.', ['code' => $companyCode]);
                $this->logLicenseEvent($conn, 'license_denied_tampered', $result);

                return $this->deny('This company\'s license is invalid. Please contact support.', 403);

            default:
                return null;
        }
    }

    /**
     * Only state-transition/failure events are audited here, not every successful
     * per-request check — that would flood audit_logs with one row per page view.
     * Deduped per session so a grace-period banner shown on every page of one visit
     * doesn't write a row per click either.
     */
    private function logLicenseEvent(BaseConnection $conn, string $action, array $result): void
    {
        $sessionKey = 'license_event_logged_' . $action;
        if (session($sessionKey)) {
            return;
        }
        session()->set($sessionKey, true);

        // An audit-write failure must never turn a license denial into a 500.
        try {
            (new \App\Services\AuditService($conn))->log($action, 'license', 'license', $result['license']['id'] ?? null, null, [
                'state' => $result['state'], 'message' => $result['message'], 'from_cache' => $result['fromCache'],
            ]);
        } catch (Throwable $e) {
            log_message('error', 'License audit write failed: {msg}', ['msg' => $e->getMessage()]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }

    private function deny(string $message, int $statusCode)
    {
        return service('response')
            ->setStatusCode($statusCode)
            ->setBody(view('errors/tenant', ['message' => $message, 'statusCode' => $statusCode]));
    }
}
