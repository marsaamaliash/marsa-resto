<?php

// database/seeders/EmployeeJobTitleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeJobTitleSeeder extends Seeder
{
    public function run()
    {
        DB::table('employee_job_title')->insert([
            ['employee_nip' => '202509010002', 'job_title_id' => 1, 'holding_id' => 1], // Programmer di HQ
            ['employee_nip' => '202509010002', 'job_title_id' => 1, 'holding_id' => 6], // Programmer di Campus
            ['employee_nip' => '202509010002', 'job_title_id' => 2, 'holding_id' => 1], // Kepala Divisi SD di HQ
        ]);
    }
}
