<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModuleOverrideModel extends Model
{
    protected $table         = 'company_module_overrides';
    protected $primaryKey    = null;
    protected $useTimestamps = true;
    protected $returnType    = 'array';
    protected $allowedFields = ['company_id', 'module_id', 'is_enabled'];

    public function forCompany(int $companyId): array
    {
        $rows = $this->where('company_id', $companyId)->findAll();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['module_id']] = (bool) $row['is_enabled'];
        }

        return $map;
    }

    public function setOverride(int $companyId, int $moduleId, ?bool $isEnabled): void
    {
        if ($isEnabled === null) {
            $this->where('company_id', $companyId)->where('module_id', $moduleId)->delete();
            return;
        }

        $existing = $this->where('company_id', $companyId)->where('module_id', $moduleId)->first();

        if ($existing) {
            $this->db->table($this->table)
                ->where('company_id', $companyId)
                ->where('module_id', $moduleId)
                ->update(['is_enabled' => $isEnabled ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')]);
            return;
        }

        $this->insert([
            'company_id' => $companyId,
            'module_id'  => $moduleId,
            'is_enabled' => $isEnabled ? 1 : 0,
        ]);
    }
}
