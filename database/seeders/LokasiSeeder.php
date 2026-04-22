<?php

namespace Database\Seeders;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    public function run(): void
    {
        $branchId = BranchSeeder::getFirstBranchId();

        Rst_MasterLokasi::insert([
            [
                'branch_id' => $branchId,
                'name' => 'Gudang Basah',
                'code' => 'WH-MAIN',
                'type' => 'warehouse',
                'created_at' => now(),
            ],
            [
                'branch_id' => $branchId,
                'name' => 'Gudang Kering',
                'code' => 'WH-DRY',
                'type' => 'kitchen',
                'created_at' => now(),
            ],
            [
                'branch_id' => $branchId,
                'name' => 'Internal Transit',
                'code' => 'TRNS-01',
                'type' => 'transit',
                'created_at' => now(),
            ],
        ]);

        $this->command->info('LokasiSeeder completed with branch_id.');
    }
}
