<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            ['holding_id' => 1, 'name' => 'Finance', 'code' => 'FIN'],
            ['holding_id' => 1, 'name' => 'IT', 'code' => 'IT'],
            ['holding_id' => 1, 'name' => 'HR', 'code' => 'HR'],
        ]);
    }
}
