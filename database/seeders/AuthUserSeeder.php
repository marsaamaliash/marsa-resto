<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthUserSeeder extends Seeder
{
    public function run(): void
    {
        $identities = DB::table('auth_identities')->get();

        foreach ($identities as $identity) {
            DB::table('auth_users')->insert([
                'identity_id' => $identity->id,
                'username' => $identity->identity_key, // LOGIN FIELD
                'email' => match ($identity->identity_key) {
                    '20231111 1 001' => 'ferdy@sccr.id',
                    '20250901 1 002' => 'admin@sccr.id',
                    '20250717 1 003' => 'fuad@sccr.id',
                    '20240103 2 001' => 'retno.w@sccr.id',
                    '20250728 1 001' => 'gilang@sccr.id',
                    '20240704 2 001' => 'ifa@sccr.id',
                    '202002011001' => 'rektor@campus.id',
                    '202532100025' => 'melati@campus.id',
                    default => null,
                },
                'password' => Hash::make('12345678'),
                'is_locked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
