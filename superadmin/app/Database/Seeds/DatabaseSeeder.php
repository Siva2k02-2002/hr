<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(PlatformRbacSeeder::class);
        $this->call(PlansSeeder::class);
        $this->call(ModulesSeeder::class);
        $this->call(PlanModulesSeeder::class);
    }
}
