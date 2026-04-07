<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HoldingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('holdings')->insert([
            ['name' => 'SCCR', 'code' => 'HQ'],
            ['name' => 'Clinic', 'code' => 'CL'],
            ['name' => 'Resort', 'code' => 'RS'],
            ['name' => 'Resto', 'code' => 'RM'],
            ['name' => 'Farm', 'code' => 'FM'],
            ['name' => 'Campus', 'code' => 'CP'],
            ['name' => 'Hospital', 'code' => 'HS'],
        ]);
    }
}
