<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $plans = [
            ['code' => 'basic',        'name' => 'Basic',        'employee_limit' => 25,  'branch_limit' => 1,  'storage_limit_mb' => 1024,  'duration_days' => 365, 'is_active' => 1],
            ['code' => 'professional', 'name' => 'Professional', 'employee_limit' => 100, 'branch_limit' => 5,  'storage_limit_mb' => 5120,  'duration_days' => 365, 'is_active' => 1],
            ['code' => 'enterprise',   'name' => 'Enterprise',   'employee_limit' => 500, 'branch_limit' => 25, 'storage_limit_mb' => 20480, 'duration_days' => 365, 'is_active' => 1],
        ];

        foreach ($plans as $plan) {
            $exists = $this->db->table('plans')->where('code', $plan['code'])->get()->getRowArray();
            if ($exists) {
                continue;
            }
            $this->db->table('plans')->insert($plan + ['created_at' => $now, 'updated_at' => $now]);
        }
    }
}
