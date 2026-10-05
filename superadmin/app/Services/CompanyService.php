<?php

namespace App\Services;

use App\Models\CompanyDatabaseConnectionModel;
use App\Models\CompanyDomainModel;
use App\Models\CompanyModel;
use RuntimeException;

/**
 * Owns company creation and status transitions. Tenant database provisioning
 * (actually creating hrms_<code> and running the template migrations) is a
 * Phase 2 concern — here we only record the *intent* to provision, as a
 * pending connection row, so Phase 2 has something to act on.
 */
class CompanyService
{
    public function __construct(
        private CompanyModel $companies = new CompanyModel(),
        private CompanyDomainModel $domains = new CompanyDomainModel(),
        private CompanyDatabaseConnectionModel $connections = new CompanyDatabaseConnectionModel(),
        private AuditService $audit = new AuditService(),
    ) {
    }

    public function create(array $data, string $subdomain, string $baseDomain): int
    {
        if ($this->domains->isReserved($subdomain)) {
            throw new RuntimeException("\"{$subdomain}\" is a reserved subdomain and cannot be assigned to a company.");
        }

        $db = db_connect();
        $db->transStart();

        $this->companies->insert($data);
        $companyId = $this->companies->getInsertID();

        $domain = strtolower($subdomain) . '.' . $baseDomain;
        $this->domains->insert([
            'company_id' => $companyId,
            'domain'     => $domain,
            'is_primary' => 1,
        ]);

        $this->connections->insert([
            'company_id'      => $companyId,
            'db_host'         => env('database.default.hostname', '127.0.0.1'),
            'db_port'         => (int) env('database.default.port', 3306),
            'db_name'         => 'hrms_' . $data['code'],
            'db_username'     => 'pending',
            'db_password_enc' => base64_encode(service('encrypter')->encrypt('')),
            'status'          => 'pending',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Failed to create company — transaction rolled back.');
        }

        $this->audit->log('create', 'company', 'company', $companyId, null, $data + ['domain' => $domain]);

        return $companyId;
    }

    public function update(int $companyId, array $data): void
    {
        $old = $this->companies->find($companyId);
        $this->companies->update($companyId, $data);
        $this->audit->log('update', 'company', 'company', $companyId, $old, $data, $companyId);
    }

    public function setStatus(int $companyId, string $status): void
    {
        $old = $this->companies->find($companyId);
        $this->companies->update($companyId, ['status' => $status]);
        $this->audit->log(
            'status_change',
            'company',
            'company',
            $companyId,
            ['status' => $old['status'] ?? null],
            ['status' => $status],
            $companyId
        );
    }

    public function archive(int $companyId): void
    {
        $old = $this->companies->find($companyId);
        $this->companies->delete($companyId);
        $this->audit->log('archive', 'company', 'company', $companyId, $old, null, $companyId);
    }
}
