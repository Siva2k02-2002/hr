<?php

namespace App\Services;

use RuntimeException;

/**
 * Verifies the license Super Admin issues for a company (see that app's
 * LicenseService) — an additional, independently-signed check layered on
 * top of TenantResolver's existing company/subscription/expiry gate, not a
 * replacement for it. Reads the platform DB's `licenses` table over the
 * same read-only `master` connection TenantResolver already uses.
 *
 * Signature is HMAC-SHA256 over the same canonical string Super Admin signs
 * with (license_uuid|company_id|plan_id|domain|issued_at|expires_at|status),
 * using license.signingSecret — identical in both apps' .env, the same
 * shared-secret pattern as encryption.key. Verifying locally means
 * this app never needs to call out to Super Admin at request time.
 */
class LicenseVerificationService
{
    private const CACHE_PREFIX = 'license_offline_';

    /**
     * @return array{state: string, license: ?array, daysLeft: ?int, graceDaysLeft: ?int, message: string, fromCache: bool}
     *
     * state is one of: valid | grace | expired | revoked | domain_mismatch | tampered | no_license
     */
    public function verify(int $companyId, string $companyCode, string $host): array
    {
        try {
            $license = db_connect('master')->table('licenses')
                ->where('company_id', $companyId)
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();

            if (! $license) {
                return $this->result('no_license', null, 'No license has been issued for this company yet.');
            }

            $this->cacheLicense($companyCode, $license);

            return $this->evaluate($license, $host, fromCache: false);
        } catch (\Throwable $e) {
            log_message('warning', 'License verification could not reach the platform DB, falling back to offline cache: {msg}', ['msg' => $e->getMessage()]);

            return $this->verifyFromCache($companyCode, $host);
        }
    }

    private function verifyFromCache(string $companyCode, string $host): array
    {
        $cached = cache()->get(self::CACHE_PREFIX . $companyCode);
        if (! $cached) {
            return $this->result('no_license', null, 'License could not be verified (platform unreachable, no offline cache available).');
        }

        $maxAgeHours = (int) (env('license.offlineCacheHours') ?: 72);
        if (strtotime($cached['cached_at']) < strtotime("-{$maxAgeHours} hours")) {
            return $this->result('no_license', null, 'Offline license cache has expired — reconnect to the platform to re-verify.');
        }

        return $this->evaluate($cached['license'], $host, fromCache: true);
    }

    private function evaluate(array $license, string $host, bool $fromCache): array
    {
        $expected = $this->sign($license);
        if (! hash_equals($expected, (string) $license['signature'])) {
            return $this->result('tampered', $license, 'License signature is invalid — this record may have been tampered with.', $fromCache);
        }

        if ($license['status'] === 'revoked') {
            return $this->result('revoked', $license, 'This license has been revoked.', $fromCache);
        }

        // Subdomain-of-domain match, not just exact equality, so a company's
        // primary domain "abc.hrms.test" still matches a request to a
        // same-company subdomain without needing a license per subdomain.
        $licenseDomain = strtolower($license['domain']);
        $requestHost   = strtolower($host);
        if ($requestHost !== $licenseDomain && ! str_ends_with($requestHost, '.' . $licenseDomain)) {
            return $this->result('domain_mismatch', $license, 'This license is not valid for this domain.', $fromCache);
        }

        $now      = time();
        $expires  = strtotime($license['expires_at']);
        $graceEnd = strtotime('+' . (int) $license['grace_days'] . ' days', $expires);

        if ($now <= $expires) {
            $daysLeft = (int) ceil(($expires - $now) / DAY);

            return $this->result('valid', $license, 'License is valid.', $fromCache, $daysLeft);
        }

        if ($now <= $graceEnd) {
            $graceDaysLeft = (int) ceil(($graceEnd - $now) / DAY);

            return $this->result('grace', $license, 'License has expired and is in its grace period.', $fromCache, null, $graceDaysLeft);
        }

        return $this->result('expired', $license, 'License has expired.', $fromCache);
    }

    private function cacheLicense(string $companyCode, array $license): void
    {
        $maxAgeHours = (int) (env('license.offlineCacheHours') ?: 72);
        cache()->save(self::CACHE_PREFIX . $companyCode, [
            'license'   => $license,
            'cached_at' => date('Y-m-d H:i:s'),
        ], $maxAgeHours * HOUR);
    }

    /** Exact same canonical string + secret Super Admin's LicenseService::sign() uses. */
    private function sign(array $license): string
    {
        $secret = env('license.signingSecret');
        if (! $secret) {
            throw new RuntimeException('license.signingSecret is not configured.');
        }

        $payload = implode('|', [
            $license['license_uuid'], $license['company_id'], $license['plan_id'],
            $license['domain'], $license['issued_at'], $license['expires_at'], $license['status'],
        ]);

        return hash_hmac('sha256', $payload, $secret);
    }

    private function result(string $state, ?array $license, string $message, bool $fromCache = false, ?int $daysLeft = null, ?int $graceDaysLeft = null): array
    {
        return [
            'state'         => $state,
            'license'       => $license,
            'daysLeft'      => $daysLeft,
            'graceDaysLeft' => $graceDaysLeft,
            'message'       => $message,
            'fromCache'     => $fromCache,
        ];
    }
}
