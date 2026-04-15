<?php

namespace Database\Seeders;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Illuminate\Database\Seeder;

class LokasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Rst_MasterLokasi::insert([
            [
                'name' => 'Gudang Basah',
                'code' => 'WH-MAIN',
                'type' => 'warehouse',
                'created_at' => now(),
            ],
            [
                'name' => 'Gudang Kering',
                'code' => 'WH-DRY',
                'type' => 'kitchen',
                'created_at' => now(),
            ],
            [
                'name' => 'Internal Transit',
                'code' => 'TRNS-01',
                'type' => 'transit',
                'created_at' => now(),
            ],
        ]);
    }
}
