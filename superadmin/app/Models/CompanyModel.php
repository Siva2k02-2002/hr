<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    protected $table         = 'companies';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $allowedFields  = [
        'code', 'name', 'status', 'plan_id', 'current_subscription_id', 'employee_limit',
        'timezone', 'currency', 'contact_name', 'contact_email', 'contact_phone', 'country',
        'trial_ends_at', 'provisioning_status', 'provisioning_error',
        'primary_admin_name', 'primary_admin_email',
    ];

    protected $validationRules = [
        'code'   => 'required|alpha_dash|max_length[50]|is_unique[companies.code,id,{id}]',
        'name'   => 'required|min_length[2]|max_length[150]',
        'plan_id'=> 'required|integer',
        'status' => 'required|in_list[trial,active,expiring,expired,suspended,cancelled]',
        'contact_email' => 'permit_empty|valid_email',
    ];

    public function withPlan()
    {
        return $this->select('companies.*, plans.name as plan_name, plans.code as plan_code')
            ->join('plans', 'plans.id = companies.plan_id');
    }

    public function primaryDomain(int $companyId): ?string
    {
        $row = $this->db->table('company_domains')
            ->select('domain')
            ->where('company_id', $companyId)
            ->where('is_primary', 1)
            ->get()
            ->getRowArray();

        return $row['domain'] ?? null;
    }

    public function counts(): array
    {
        $base = $this->builder()->select('status, COUNT(*) as total')->groupBy('status')->get()->getResultArray();

        $counts = ['trial' => 0, 'active' => 0, 'expiring' => 0, 'expired' => 0, 'suspended' => 0, 'cancelled' => 0];
        foreach ($base as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }
}
