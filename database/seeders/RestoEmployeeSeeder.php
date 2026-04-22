<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestoEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = BranchSeeder::getFirstBranchId();

        $employees = [
            [
                'branch_id' => $branchId,
                'employee_number' => 'EMP001',
                'name' => 'Ahmad Fauzi',
                'department' => 'IT',
                'position' => 'Programmer',
                'daily_allowance' => 20000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => $branchId,
                'employee_number' => 'EMP002',
                'name' => 'Siti Nurhaliza',
                'department' => 'HR',
                'position' => 'HR Staff',
                'daily_allowance' => 20000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => $branchId,
                'employee_number' => 'EMP003',
                'name' => 'Budi Santoso',
                'department' => 'Finance',
                'position' => 'Akuntan',
                'daily_allowance' => 25000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => $branchId,
                'employee_number' => 'EMP004',
                'name' => 'Dewi Lestari',
                'department' => 'Marketing',
                'position' => 'Marketing Staff',
                'daily_allowance' => 20000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'branch_id' => $branchId,
                'employee_number' => 'EMP005',
                'name' => 'Rizky Pratama',
                'department' => 'Operations',
                'position' => 'Supervisor',
                'daily_allowance' => 30000.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::connection('sccr_resto')->table('employees')->insert($employees);

        $this->command->info('RestoEmployeeSeeder completed with branch_id.');
    }
}
