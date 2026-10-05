<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table         = 'plans';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'code', 'name', 'employee_limit', 'branch_limit', 'storage_limit_mb',
        'duration_days', 'grace_days', 'is_active',
    ];

    protected $validationRules = [
        'code'             => 'required|alpha_dash|max_length[50]',
        'name'             => 'required|min_length[2]|max_length[100]',
        'employee_limit'   => 'required|integer|greater_than[0]',
        'branch_limit'     => 'required|integer|greater_than[0]',
        'storage_limit_mb' => 'required|integer|greater_than[0]',
        'duration_days'    => 'required|integer|greater_than[0]',
        'grace_days'       => 'permit_empty|integer|greater_than_equal_to[0]',
    ];

    public function moduleIds(int $planId): array
    {
        $rows = $this->db->table('plan_modules')->select('module_id')->where('plan_id', $planId)->get()->getResultArray();

        return array_map('intval', array_column($rows, 'module_id'));
    }

    public function syncModules(int $planId, array $moduleIds): void
    {
        $this->db->table('plan_modules')->where('plan_id', $planId)->delete();

        if ($moduleIds === []) {
            return;
        }

        $rows = array_map(static fn ($id) => [
            'plan_id'    => $planId,
            'module_id'  => (int) $id,
            'created_at' => date('Y-m-d H:i:s'),
        ], $moduleIds);

        $this->db->table('plan_modules')->insertBatch($rows);
    }
}
