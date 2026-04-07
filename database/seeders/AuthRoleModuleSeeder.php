<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthRoleModuleSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * DEV (id = 1)
         * Full access semua module
         */
        DB::table('auth_role_modules')->insert([
            [
                'role_id' => 1, // DEV
                'module_code' => '01001',
                'access_level' => 'full',
                'is_active' => true,
            ],
            [
                'role_id' => 1,
                'module_code' => '01005',
                'access_level' => 'full',
                'is_active' => true,
            ],
            [
                'role_id' => 1,
                'module_code' => '03001',
                'access_level' => 'full',
                'is_active' => true,
            ],
        ]);

        /**
         * STAFF (id = 5)
         * Inventaris hanya view
         */
        DB::table('auth_role_modules')->insert([
            [
                'role_id' => 5,
                'module_code' => '01005',
                'access_level' => 'view',
                'is_active' => true,
            ],
        ]);
    }
}
