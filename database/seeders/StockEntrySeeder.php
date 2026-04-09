<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Resto\StockService;
use Illuminate\Support\Facades\DB;

class StockEntrySeeder extends Seeder
{
    public function run()
    {
        // Gunakan koneksi sccr_resto sesuai migration kamu
        $conn = DB::connection('sccr_resto');

        // 1. Pastikan Data Master Dummy Ada (Jika belum ada)
        // Kita gunakan ID tetap agar mudah untuk testing movement nanti
        
        // Buat UOM
        $uomId = $conn->table('uoms')->updateOrInsert(['id' => 1], ['name' => 'Kilogram', 'symbols' => 'Kg']);
        
        // Buat Lokasi (Gudang Utama & Dapur)
        $conn->table('locations')->updateOrInsert(['id' => 1], ['name' => 'Main Warehouse', 'type' => 'warehouse']);
        $conn->table('locations')->updateOrInsert(['id' => 2], ['name' => 'Kitchen', 'type' => 'kitchen']);
        
        // Buat Item Bahan Baku
        $items = [
            ['id' => 1, 'name' => 'Daging Ayam', 'uom_id' => 1],
            ['id' => 2, 'name' => 'Beras Putih', 'uom_id' => 1],
            ['id' => 3, 'name' => 'Minyak Goreng', 'uom_id' => 1],
        ];

        foreach ($items as $item) {
            $conn->table('items')->updateOrInsert(['id' => $item['id']], [
                'name' => $item['name'],
                'uom_id' => $item['uom_id']
            ]);
        }

        // 2. Masukkan Stok Awal menggunakan StockService
        // Kita simulasikan barang masuk (Procurement) ke Main Warehouse (ID 1)
        
        $stockToInput = [
            ['item_id' => 1, 'qty' => 50, 'note' => 'Initial Stock - Ayam'],
            ['item_id' => 2, 'qty' => 100, 'note' => 'Initial Stock - Beras'],
            ['item_id' => 3, 'qty' => 25, 'note' => 'Initial Stock - Minyak'],
        ];

        foreach ($stockToInput as $data) {
            StockService::addMutation(
                $data['item_id'],
                1, // location_id: Main Warehouse
                1, // uom_id: Kg
                $data['qty'],
                'in', // type sesuai enum kamu
                'PROCUREMENT_MOCK', // reference_type
                rand(1000, 9999),    // reference_id dummy
                $data['note']
            );
        }

        $this->command->info('Mock Procurement berhasil! Stok tersedia di Main Warehouse.');
    }
}