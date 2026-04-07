<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            [
                'employee_nip' => '20250901 1 002',
                'email' => 'admin@sccr.id',
                'password' => Hash::make('987654321'),
            ],
            [
                'employee_nip' => '20250717 1 003',
                'email' => 'fuad@sccr.id',
                'password' => Hash::make('12345678'),
            ],
            [
                'employee_nip' => '20240103 2 001',
                'email' => 'retno.w@sccr.id',
                'password' => Hash::make('12345678'),
            ],
            [
                'employee_nip' => '20250728 1 001',
                'email' => 'gilang@sccr.id',
                'password' => Hash::make('12345678'),
            ],
            [
                'employee_nip' => '20240704 2 001',
                'email' => 'ifa@sccr.id',
                'password' => Hash::make('12345678'),
            ],
        ]);
    }
}
