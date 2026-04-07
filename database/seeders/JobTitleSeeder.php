<?php

namespace Database\Seeders;

// database/seeders/JobTitleSeeder.php

use App\Models\JobTitle;
use Illuminate\Database\Seeder;

class JobTitleSeeder extends Seeder
{
    public function run()
    {
        $titles = [
            ['name' => 'Programmer', 'description' => 'Developer aplikasi internal'],
            ['name' => 'Kepala Divisi System Development', 'description' => 'Memimpin tim pengembangan sistem'],
            ['name' => 'Finance Analyst', 'description' => 'Analisis laporan keuangan'],
        ];

        foreach ($titles as $title) {
            JobTitle::create($title);
        }
    }
}
