<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ModulesSeeder extends Seeder
{
    /** module_key => [name, is_core] */
    private const MODULES = [
        'employee'      => ['Employee Management', 1],
        'attendance'    => ['Attendance', 1],
        'leave'         => ['Leave', 1],
        'payroll'       => ['Payroll', 0],
        'recruitment'   => ['Recruitment', 0],
        'performance'   => ['Performance', 0],
        'assets'        => ['Assets', 0],
        'expenses'      => ['Expenses', 0],
        'documents'     => ['Documents', 1],
        'reports'       => ['Reports', 1],
        'training'      => ['Training', 0],
        'notifications' => ['Notifications', 1],
    ];

    public function run()
    {
        $now = date('Y-m-d H:i:s');

        foreach (self::MODULES as $key => [$name, $isCore]) {
            $exists = $this->db->table('modules')->where('module_key', $key)->get()->getRowArray();
            if ($exists) {
                continue;
            }
            $this->db->table('modules')->insert([
                'module_key' => $key,
                'name'       => $name,
                'is_core'    => $isCore,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
