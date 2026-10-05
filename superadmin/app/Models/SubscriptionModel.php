<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table         = 'subscriptions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'company_id', 'plan_id', 'starts_at', 'expires_at', 'employee_limit_override',
        'status', 'remarks', 'created_by', 'last_reminder_sent_at',
    ];

    protected $validationRules = [
        'company_id' => 'required|integer',
        'plan_id'    => 'required|integer',
        'starts_at'  => 'required|valid_date',
        'expires_at' => 'required|valid_date',
        'status'     => 'required|in_list[trial,active,expiring,expired,suspended,cancelled]',
    ];

    public function withCompanyAndPlan()
    {
        return $this->select('subscriptions.*, companies.name as company_name, companies.code as company_code, plans.name as plan_name')
            ->join('companies', 'companies.id = subscriptions.company_id')
            ->join('plans', 'plans.id = subscriptions.plan_id');
    }

    public function expiringWithin(int $days): array
    {
        $cutoff = date('Y-m-d', strtotime("+{$days} days"));

        return $this->withCompanyAndPlan()
            ->whereIn('subscriptions.status', ['active', 'expiring'])
            ->where('subscriptions.expires_at <=', $cutoff)
            ->orderBy('subscriptions.expires_at', 'ASC')
            ->findAll();
    }
}
