<?php

namespace App\Services;

use App\Models\CompanyDomainModel;
use App\Models\LicenseModel;
use App\Models\PlanModel;
use RuntimeException;

/**
 * Issues and manages the signed license record layered on top of the
 * existing company/subscription/provisioning system (Phase 11) — it doesn't
 * replace any of that. A company's actual day-to-day access was, and still
 * is, gated by TenantResolver's own company/subscription/expiry checks; the
 * license is an additional, independently-verifiable artifact HRMS checks
 * alongside that, with its own signature, revocation, and offline story.
 *
 * Signing secret: license.signingSecret, identical in both apps' .env —
 * the same shared-secret pattern already used for encryption.key. HRMS must
 * hold the real secret to verify a
 * license fully offline (no network/platform-DB call needed); "never expose
 * the signing secret" means never returning it in any response/view/log,
 * not that the verifier can't have it.
 */
class LicenseService
{
    public function __construct(
        private LicenseModel $licenses = new LicenseModel(),
        private CompanyDomainModel $domains = new CompanyDomainModel(),
        private PlanModel $plans = new PlanModel(),
        private AuditService $audit = new AuditService(),
    ) {
    }

    /** Called once a company finishes provisioning (see CompanyProvisioningService::runProvisioning()). */
    public function generate(int $companyId, int $planId, string $expiresAt, int $issuedBy): array
    {
        $domain = $this->domains->where('company_id', $companyId)->where('is_primary', 1)->first();
        if (! $domain) {
            throw new RuntimeException('Cannot issue a license: company has no primary domain.');
        }

        $plan = $this->plans->find($planId);
        if (! $plan) {
            throw new RuntimeException('Cannot issue a license: plan not found.');
        }

        return $this->issue($companyId, $planId, $domain['domain'], date('Y-m-d H:i:s'), $expiresAt, (int) $plan['grace_days'], $issuedBy);
    }

    /** Renewal inserts a new license row (preserves full history) rather than mutating the old one — old row stays exactly as it was signed. */
    public function renew(int $companyId, string $newExpiresAt, int $issuedBy): array
    {
        $current = $this->licenses->currentFor($companyId);
        if (! $current) {
            throw new RuntimeException('This company has no existing license to renew — generate one first.');
        }

        $license = $this->issue($companyId, (int) $current['plan_id'], $current['domain'], date('Y-m-d H:i:s'), $newExpiresAt, (int) $current['grace_days'], $issuedBy);

        $this->audit->log('license_renew', 'license', 'license', $license['id'],
            ['expires_at' => $current['expires_at']], ['expires_at' => $newExpiresAt], $companyId);

        return $license;
    }

    public function revoke(int $licenseId, int $revokedBy, string $reason): void
    {
        $license = $this->licenses->find($licenseId);
        if (! $license) {
            throw new RuntimeException('License not found.');
        }
        if ($license['status'] === 'revoked') {
            throw new RuntimeException('This license is already revoked.');
        }

        $update = ['status' => 'revoked', 'revoked_at' => date('Y-m-d H:i:s'), 'revoked_by' => $revokedBy, 'revoked_reason' => $reason];
        $update['signature'] = $this->sign($license['license_uuid'], (int) $license['company_id'], (int) $license['plan_id'], $license['domain'], $license['issued_at'], $license['expires_at'], 'revoked');
        $this->licenses->update($licenseId, $update);

        $this->audit->log('license_revoke', 'license', 'license', $licenseId, ['status' => $license['status']], ['status' => 'revoked', 'reason' => $reason], (int) $license['company_id']);
    }

    private function issue(int $companyId, int $planId, string $domain, string $issuedAt, string $expiresAt, int $graceDays, int $issuedBy): array
    {
        // licenses.issued_at / expires_at are DATETIME columns, and HRMS verifies against the
        // value read back from them. Callers may pass a bare DATE (subscriptions.expires_at is
        // DATE), so normalise BEFORE signing: the signed string must equal the stored string.
        $issuedAt  = $this->canonicalDateTime($issuedAt);
        $expiresAt = $this->canonicalDateTime($expiresAt);

        $uuid = $this->uuidV4();
        $key  = $this->humanKey();
        $sig  = $this->sign($uuid, $companyId, $planId, $domain, $issuedAt, $expiresAt, 'active');

        $id = $this->licenses->insert([
            'license_uuid' => $uuid,
            'license_key'  => $key,
            'company_id'   => $companyId,
            'plan_id'      => $planId,
            'domain'       => $domain,
            'issued_at'    => $issuedAt,
            'expires_at'   => $expiresAt,
            'grace_days'   => $graceDays,
            'status'       => 'active',
            'signature'    => $sig,
            'issued_by'    => $issuedBy,
        ], true);

        $this->audit->log('license_issue', 'license', 'license', $id, null, [
            'license_key' => $key, 'domain' => $domain, 'expires_at' => $expiresAt,
        ], $companyId);

        return $this->licenses->find($id);
    }

    /** Exact same canonical string + HMAC-SHA256 HRMS's LicenseVerificationService recomputes to verify — keep both in lockstep if this ever changes. */
    private function sign(string $uuid, int $companyId, int $planId, string $domain, string $issuedAt, string $expiresAt, string $status): string
    {
        $secret = env('license.signingSecret');
        if (! $secret) {
            throw new RuntimeException('license.signingSecret is not configured — cannot sign a license.');
        }

        $payload = implode('|', [$uuid, $companyId, $planId, $domain, $issuedAt, $expiresAt, $status]);

        return hash_hmac('sha256', $payload, $secret);
    }

    /** The one canonical form that is both signed and stored: 'Y-m-d H:i:s' (a bare 'Y-m-d' becomes midnight). */
    private function canonicalDateTime(string $value): string
    {
        $ts = strtotime($value);
        if ($ts === false) {
            throw new RuntimeException('Invalid license date: ' . $value);
        }

        return date('Y-m-d H:i:s', $ts);
    }

    private function uuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function humanKey(): string
    {
        $groups = [];
        for ($i = 0; $i < 4; $i++) {
            $groups[] = strtoupper(bin2hex(random_bytes(2)));
        }

        return 'HRMS-' . implode('-', $groups);
    }
}
