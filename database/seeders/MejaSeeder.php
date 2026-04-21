<?php

namespace Database\Seeders;

use App\Models\Holdings\Resto\Master\Rst_Meja;
use Illuminate\Database\Seeder;

class MejaSeeder extends Seeder
{
    public function run(): void
    {
        $mejaData = [
            [
                'table_number' => '01',
                'capacity' => 2,
                'area' => 'indoor',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Meja untuk 2 orang, dekat jendela',
            ],
            [
                'table_number' => '02',
                'capacity' => 4,
                'area' => 'indoor',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Meja keluarga di area indoor',
            ],
            [
                'table_number' => '03',
                'capacity' => 4,
                'area' => 'indoor',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Meja keluarga di area indoor',
            ],
            [
                'table_number' => '04',
                'capacity' => 6,
                'area' => 'indoor',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Meja besar untuk grup',
            ],
            [
                'table_number' => '05',
                'capacity' => 2,
                'area' => 'outdoor',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Meja taman untuk 2 orang',
            ],
            [
                'table_number' => '06',
                'capacity' => 4,
                'area' => 'outdoor',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Meja taman keluarga',
            ],
            [
                'table_number' => '07',
                'capacity' => 8,
                'area' => 'vip',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Ruang VIP untuk acara spesial',
            ],
            [
                'table_number' => '08',
                'capacity' => 4,
                'area' => 'smoking',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Area smoking, ventilasi baik',
            ],
            [
                'table_number' => '09',
                'capacity' => 4,
                'area' => 'non-smoking',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Area non-smoking, nyaman',
            ],
            [
                'table_number' => '10',
                'capacity' => 2,
                'area' => 'non-smoking',
                'status' => 'available',
                'is_active' => true,
                'notes' => 'Meja cozy untuk pasangan',
            ],
        ];

        foreach ($mejaData as $data) {
            Rst_Meja::updateOrCreate(
                ['table_number' => $data['table_number']],
                $data
            );
        }
    }
}
