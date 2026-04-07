<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('positions')->insert([
            ['level' => 0, 'title' => 'President Director'],
            ['level' => 1, 'title' => 'Director'],
            ['level' => 2, 'title' => 'Manager'],
            ['level' => 3, 'title' => 'Head Division'],
            ['level' => 4, 'title' => 'Staff'],
        ]);
    }
}
