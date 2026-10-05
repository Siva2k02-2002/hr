<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenseModel extends Model
{
    protected $table         = 'licenses';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'license_uuid', 'license_key', 'company_id', 'plan_id', 'domain', 'issued_at', 'expires_at',
        'grace_days', 'status', 'signature', 'issued_by', 'revoked_at', 'revoked_by', 'revoked_reason',
    ];

    /** The one license HRMS/the UI should treat as authoritative for a company — most recently issued, regardless of status. */
    public function currentFor(int $companyId): ?array
    {
        return $this->where('company_id', $companyId)->orderBy('id', 'DESC')->first();
    }

    public function withCompanyAndPlan()
    {
        return $this->select('licenses.*, companies.name as company_name, companies.code as company_code, plans.name as plan_name')
            ->join('companies', 'companies.id = licenses.company_id')
            ->join('plans', 'plans.id = licenses.plan_id');
    }
}
