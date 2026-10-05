<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanModulesSeeder extends Seeder
{
    /** plan code => module_keys included by default */
    private const ASSIGNMENTS = [
        'basic'        => ['employee', 'attendance', 'leave', 'documents', 'reports', 'notifications'],
        'professional' => ['employee', 'attendance', 'leave', 'payroll', 'recruitment', 'documents', 'reports', 'notifications', 'assets', 'expenses'],
        'enterprise'   => ['employee', 'attendance', 'leave', 'payroll', 'recruitment', 'performance', 'assets', 'expenses', 'documents', 'reports', 'training', 'notifications'],
    ];

    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $plans   = $this->db->table('plans')->select('id, code')->get()->getResultArray();
        $modules = $this->db->table('modules')->select('id, module_key')->get()->getResultArray();

        $planIdByCode   = array_column($plans, 'id', 'code');
        $moduleIdByKey  = array_column($modules, 'id', 'module_key');

        foreach (self::ASSIGNMENTS as $planCode => $moduleKeys) {
            if (! isset($planIdByCode[$planCode])) {
                continue;
            }
            $planId = $planIdByCode[$planCode];

            foreach ($moduleKeys as $moduleKey) {
                if (! isset($moduleIdByKey[$moduleKey])) {
                    continue;
                }
                $moduleId = $moduleIdByKey[$moduleKey];

                $exists = $this->db->table('plan_modules')
                    ->where('plan_id', $planId)
                    ->where('module_id', $moduleId)
                    ->get()->getRowArray();

                if ($exists) {
                    continue;
                }

                $this->db->table('plan_modules')->insert([
                    'plan_id'    => $planId,
                    'module_id'  => $moduleId,
                    'created_at' => $now,
                ]);
            }
        }
    }
}
