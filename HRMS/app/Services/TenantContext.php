<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/**
 * Holds the current request's resolved tenant identity. Populated exactly
 * once per request by TenantResolver, before any tenant-aware controller,
 * service, or model runs. Nothing else may set these values — in
 * particular, tenant identity is never read from session, GET, POST, or
 * any client-supplied value; only TenantResolver, which derives it solely
 * from the request hostname, may call set().
 */
class TenantContext
{
    private bool $resolved = false;
    private int $companyId;
    private string $companyCode;
    private string $companyName;
    private string $domain;
    private string $subscriptionStatus;
    private BaseConnection $db;

    public function set(
        int $companyId,
        string $companyCode,
        string $companyName,
        string $domain,
        string $subscriptionStatus,
        BaseConnection $db
    ): void {
        $this->companyId          = $companyId;
        $this->companyCode        = $companyCode;
        $this->companyName        = $companyName;
        $this->domain             = $domain;
        $this->subscriptionStatus = $subscriptionStatus;
        $this->db                 = $db;
        $this->resolved           = true;
    }

    public function isResolved(): bool
    {
        return $this->resolved;
    }

    private function assertResolved(): void
    {
        if (! $this->resolved) {
            throw new RuntimeException('TenantContext accessed before TenantResolver ran for this request.');
        }
    }

    public function companyId(): int
    {
        $this->assertResolved();

        return $this->companyId;
    }

    public function companyCode(): string
    {
        $this->assertResolved();

        return $this->companyCode;
    }

    public function companyName(): string
    {
        $this->assertResolved();

        return $this->companyName;
    }

    public function domain(): string
    {
        $this->assertResolved();

        return $this->domain;
    }

    public function subscriptionStatus(): string
    {
        $this->assertResolved();

        return $this->subscriptionStatus;
    }

    public function db(): BaseConnection
    {
        $this->assertResolved();

        return $this->db;
    }
}
