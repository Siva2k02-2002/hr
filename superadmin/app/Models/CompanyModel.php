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

    /**
     * Joins each company to its CURRENT subscription (companies.current_subscription_id -
     * the same pointer TenantResolver follows), and only if that subscription really
     * belongs to the company. A company with a missing/foreign pointer simply gets no row.
     */
    public function withCurrentSubscription()
    {
        return $this->join('subscriptions cs', 'cs.id = companies.current_subscription_id AND cs.company_id = companies.id', 'left');
    }

    /**
     * The one definition of a company's subscription state, used by the dashboard counts
     * and the Companies list status filter (requires withCurrentSubscription()):
     *   none       - no valid current subscription
     *   suspended / cancelled - as recorded
     *   expired    - status 'expired', or expires_at before today (expiry day itself is still valid,
     *                the same rule as TenantResolver: expires_at < today is denied)
     *   scheduled  - start date still in the future
     *   trial / active / expiring - as recorded, and in date
     * $today is a Y-m-d string so every comparison uses the application's date, not the DB clock.
     */
    public function effectiveStatusSql(string $today): string
    {
        $t = $this->db->escape($today);

        return "CASE
            WHEN cs.id IS NULL THEN 'none'
            WHEN cs.status IN ('suspended', 'cancelled') THEN cs.status
            WHEN cs.status = 'expired' OR cs.expires_at < {$t} THEN 'expired'
            WHEN cs.starts_at > {$t} THEN 'scheduled'
            ELSE cs.status
        END";
    }

    public function whereEffectiveStatus(string $status, string $today)
    {
        return $this->where($this->effectiveStatusSql($today) . ' = ' . $this->db->escape($status), null, false);
    }

    public function counts(?string $today = null): array
    {
        $today = $today ?? date('Y-m-d');
        $expr  = $this->effectiveStatusSql($today);

        $rows = $this->withCurrentSubscription()
            ->select("{$expr} AS effective_status, COUNT(*) AS total", false)
            ->where('companies.deleted_at', null)
            ->groupBy('effective_status')
            ->get()->getResultArray();

        $counts = ['trial' => 0, 'active' => 0, 'expiring' => 0, 'expired' => 0, 'suspended' => 0, 'cancelled' => 0, 'scheduled' => 0, 'none' => 0];
        foreach ($rows as $row) {
            $counts[$row['effective_status']] = (int) $row['total'];
        }

        return $counts;
    }
}
