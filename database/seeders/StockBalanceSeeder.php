<?php

namespace Database\Seeders;

use App\Services\Resto\StockService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ambil semua data master dari koneksi sccr_resto
        $items = DB::connection('sccr_resto')->table('items')->get();
        $locations = DB::connection('sccr_resto')->table('locations')->pluck('id');

        // Pastikan ada data master sebelum lanjut
        if ($items->isEmpty() || $locations->isEmpty()) {
            $this->command->error('Data Items atau Locations kosong! Jalankan seeder Master dulu.');

            return;
        }

        $this->command->info('Memulai seeding stok ke '.$locations->count().' lokasi...');

        // 2. Loop setiap item untuk dimasukkan ke setiap lokasi
        foreach ($items as $item) {
            foreach ($locations as $locationId) {

                // Kita gunakan StockService::addMutation agar:
                // - stock_balances terisi (qty_available)
                // - stock_mutations tercatat (type: 'in')
                // - Konsisten dengan logic 'No Negative Stock'

                StockService::addMutation(
                    $item->id,          // itemId
                    $locationId,        // locationId
                    $item->uom_id,      // uomId (diambil dari master item)
                    rand(50, 150),      // qty (angka random untuk stok awal)
                    'in',               // type (semua available sebagai stok masuk)
                    'INITIAL_ENTRY',    // reference_type
                    null,               // reference_id
                    'Seeding stok awal untuk testing movement' // notes
                );
            }

            $this->command->info("Item: {$item->name} berhasil di-seed.");
        }

        $this->command->info('Semua stok berhasil masuk sebagai "Available".');
    }
}
