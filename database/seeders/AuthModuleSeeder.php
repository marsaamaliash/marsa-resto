<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('auth_modules')->insert([
            ['code' => '01001', 'name' => 'Employee', 'route' => 'holdings.hq.sdm.hr.employee-table'],
            ['code' => '01005', 'name' => 'Inventaris', 'route' => 'holdings.hq.sdm.rt.inventaris.inventaris-table'],
            ['code' => '03001', 'name' => 'Siakad', 'route' => 'holdings.campus.siakad.student.students-table'],
        ]);
    }
}
