<?php

namespace Database\Seeders;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'name' => 'Main Warehouse',
            'code' => 'WH-MAIN',
            'type' => 'warehouse',
            'created_at' => now(),
        ],
        [
            'name' => 'Kitchen Central',
            'code' => 'KIT-01',
            'type' => 'kitchen',
            'created_at' => now(),
        ],
        [
            'name' => 'Internal Transit',
            'code' => 'TRNS-01',
            'type' => 'transit', // WAJIB ADA untuk mutasi antar lokasi
            'created_at' => now(),
        ],
    ]);
    }
}
