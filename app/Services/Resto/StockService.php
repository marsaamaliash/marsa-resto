<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use Illuminate\Support\Facades\DB;

class StockService
{
    public static function addMutation(
        $itemId, 
        $locationId, 
        $uomId, 
        $qty, 
        $type, 
        $referenceType = null, 
        $referenceId = null, 
        $notes = null,
        $fromLocationId = null,
        $toLocationId = null
    ) {
        return DB::transaction(function () use (
            $itemId, $locationId, $uomId, $qty, $type, 
            $referenceType, $referenceId, $notes, $fromLocationId, $toLocationId
        ) {
            // 1. Ambil atau inisialisasi saldo saat ini
            $balance = Rst_StockBalance::firstOrNew([
                'item_id'     => $itemId,
                'location_id' => $locationId,
            ], [
                'uom_id'        => $uomId,
                'qty_available' => 0,
                'qty_reserved'  => 0,
                'qty_waste'     => 0
            ]);

            // Simpan nilai awal untuk record mutasi
            $qtyBefore = $balance->qty_available;

            // 2. Logic Update Saldo berdasarkan Tipe Mutasi
            switch ($type) {
                case 'in':
                case 'transfer_in':
                case 'unreserve': // Unreserve mengembalikan stok ke available
                    $balance->qty_available += $qty;
                    if ($type === 'unreserve') {
                        $balance->qty_reserved -= $qty;
                    }
                    break;

                case 'out':
                case 'transfer_out':
                    $balance->qty_available -= $qty;
                    break;

                case 'reserve':
                    // Pindahkan dari Available ke Reserved
                    if ($balance->qty_available < $qty) {
                        throw new \Exception("Gagal Reserve! Stok Available tidak cukup.");
                    }
                    $balance->qty_available -= $qty;
                    $balance->qty_reserved += $qty;
                    break;

                case 'consume':
                    // Mengurangi dari Reserved (karena sudah dimasak)
                    if ($balance->qty_reserved < $qty) {
                        throw new \Exception("Gagal Consume! Stok Reserved tidak cukup.");
                    }
                    $balance->qty_reserved -= $qty;
                    break;

                case 'waste':
                    // Mengurangi dari Available, menambah record Waste (audit)
                    if ($balance->qty_available < $qty) {
                        throw new \Exception("Gagal mencatat Waste! Stok Available tidak cukup.");
                    }
                    $balance->qty_available -= $qty;
                    $balance->qty_waste += $qty;
                    break;

                case 'adjustment':
                    $balance->qty_available += $qty; // Bisa plus atau minus
                    break;
            }

            // 3. Validasi Akhir: Tidak boleh ada kolom yang negatif
            if ($balance->qty_available < 0 || $balance->qty_reserved < 0) {
                throw new \Exception("Transaksi ditolak: Saldo stok tidak mencukupi atau akan menjadi negatif.");
            }

            // 4. Catat ke Ledger (Mutations)
            Rst_StockMutation::create([
                'item_id'          => $itemId,
                'location_id'      => $locationId,
                'uom_id'           => $uomId,
                'type'             => $type,
                'qty'              => $qty,
                'qty_before'       => $qtyBefore,
                'qty_after'        => $balance->qty_available,
                'reference_type'   => $referenceType,
                'reference_id'     => $referenceId,
                'from_location_id' => $fromLocationId,
                'to_location_id'   => $toLocationId,
                'notes'            => $notes,
            ]);

            // 5. Simpan Perubahan Saldo
            $balance->save();

            return $balance;
        });
    }
}