<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('auth_permissions')->insert([
            ['code' => 'INV_VIEW', 'module_code' => '01005'],
            ['code' => 'INV_CREATE', 'module_code' => '01005'],
            ['code' => 'INV_UPDATE', 'module_code' => '01005'],
            ['code' => 'INV_DELETE', 'module_code' => '01005'],
            ['code' => 'INV_MASTER', 'module_code' => '01005'], // holding, lokasi, dll
        ]);
    }
}
