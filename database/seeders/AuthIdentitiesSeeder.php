<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthIdentitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('auth_identities')->insert([
            // Employees (SCCR)
            [
                'identity_type' => 'employee',
                'identity_key' => '20231111 1 001',
                'holding_id' => 1,
                'is_active' => true,
            ],
            [
                'identity_type' => 'employee',
                'identity_key' => '20250901 1 002',
                'holding_id' => 1,
                'is_active' => true,
            ],
            [
                'identity_type' => 'employee',
                'identity_key' => '20250717 1 003',
                'holding_id' => 1,
                'is_active' => true,
            ],
            [
                'identity_type' => 'employee',
                'identity_key' => '20240103 2 001',
                'holding_id' => 1,
                'is_active' => true,
            ],
            [
                'identity_type' => 'employee',
                'identity_key' => '20250728 1 001',
                'holding_id' => 1,
                'is_active' => true,
            ],
            [
                'identity_type' => 'employee',
                'identity_key' => '20240704 2 001',
                'holding_id' => 1,
                'is_active' => true,
            ],

            // Campus
            [
                'identity_type' => 'lecturer',
                'identity_key' => '202002011001',
                'holding_id' => 6,
                'is_active' => true,
            ],
            [
                'identity_type' => 'student',
                'identity_key' => '202532100025',
                'holding_id' => 6,
                'is_active' => true,
            ],
        ]);

    }
}
