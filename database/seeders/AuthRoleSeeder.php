<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('auth_roles')->insert([
            [
                'code' => 'DEV',
                'name' => 'Developer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'BOD',
                'name' => 'Board of Director',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'MGR',
                'name' => 'Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'HEAD',
                'name' => 'Head Division',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'STAFF',
                'name' => 'Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
