<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding branches...');

        $branches = [
            [
                'holding_id' => 1,
                'code' => 'RES-001',
                'name' => ' Sains De Resto',
                'address' => 'Jl. Cepoko Raya, Cepoko, Kec. Gn. Pati, Kota Semarang, Jawa Tengah 50223',
                'phone' => '021-12345678',
                'email' => 'sainsderesto@gmail.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holding_id' => 1,
                'code' => 'BDG-001',
                'name' => 'Bandung',
                'address' => 'Jl. Dago No. 45, Bandung',
                'phone' => '022-98765432',
                'email' => 'bandung@resto.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'holding_id' => 1,
                'code' => 'SBY-001',
                'name' => 'Surabaya',
                'address' => 'Jl. Raya Darmo No. 78, Surabaya',
                'phone' => '031-55556666',
                'email' => 'surabaya@resto.com',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($branches as $branch) {
            DB::connection('sccr_resto')->table('branches')->insertOrIgnore($branch);
        }

        $this->command->info('Branches seeded successfully: Jakarta Pusat, Bandung, Surabaya');
    }

    public static function getFirstBranchId(): int
    {
        return DB::connection('sccr_resto')->table('branches')
            ->where('code', 'RES-001')
            ->value('id') ?? 1;
    }
}
