<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('divisions')->insert([
            ['department_id' => 1, 'name' => 'Accounts Receivable', 'code' => 'AR'],
            ['department_id' => 1, 'name' => 'Accounts Payable', 'code' => 'AP'],
        ]);
    }
}
