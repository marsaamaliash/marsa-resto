<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockMutationService
{
    /**
     * Single Source of Truth untuk semua mutasi stok (Movement, Procurement, Production)
     * Tipe mutasi sesuai enum migration: in, out, transfer, consume, adjustment, waste
     */
    public static function addMutation(
        $itemId,
        $locationId,
        $uomId,
        $qty,
        $type, // in, out, transfer, consume, adjustment, waste
        $referenceNumber = null,
        $notes = null,
        $fromLocationId = null,
        $toLocationId = null,
        $userId = null
    ) {
        $userId = $userId ?: Auth::id() ?: 'SYSTEM';

        return DB::transaction(function () use (
            $itemId, $locationId, $uomId, $qty, $type,
            $referenceNumber, $notes, $fromLocationId, $toLocationId, $userId
        ) {
            // 1. Ambil saldo dengan Lock (Pencegahan Race Condition)
            // Menggunakan lockForUpdate agar row ini tidak bisa diubah transaksi lain sampai selesai
            $balance = Rst_StockBalance::where([
                'item_id' => $itemId,
                'location_id' => $locationId,
            ])->lockForUpdate()->first();

            if (! $balance) {
                $balance = new Rst_StockBalance([
                    'item_id' => $itemId,
                    'location_id' => $locationId,
                    'uom_id' => $uomId,
                    'qty_available' => 0,
                    'qty_reserved' => 0,
                    'qty_in_transit' => 0,
                    'qty_waste' => 0,
                ]);
            }

            // Simpan nilai awal untuk audit ledger
            $qtyBefore = $balance->qty_available;

            // 2. Logic Update Saldo berdasarkan Tipe Mutasi
            switch ($type) {
                case 'in': // Dari Procurement (GR)
                    $balance->qty_available += $qty;
                    break;

                case 'transfer': // Perpindahan antar lokasi (Gudang -> Dapur)
                    if ($balance->qty_available < $qty) {
                        throw new \Exception('Gagal Transfer! Stok Available tidak cukup.');
                    }
                    $balance->qty_available -= $qty;
                    break;

                case 'out': // Barang Keluar (Rusak/Expired langsung dari gudang)
                    if ($balance->qty_available < $qty) {
                        throw new \Exception('Gagal Out! Stok Available tidak cukup.');
                    }
                    $balance->qty_available -= $qty;
                    break;

                case 'consume': // Digunakan dalam Production (bahan baku habis)
                    if ($balance->qty_available < $qty) {
                        throw new \Exception('Gagal Consume! Stok Available tidak cukup.');
                    }
                    $balance->qty_available -= $qty;
                    break;

                case 'waste': // Pencatatan barang rusak/basi
                    if ($balance->qty_available < $qty) {
                        throw new \Exception('Gagal Waste! Stok Available tidak cukup.');
                    }
                    $balance->qty_available -= $qty;
                    $balance->qty_waste += $qty;
                    break;

                case 'adjustment': // Stock Opname
                    $balance->qty_available += $qty;
                    break;

                default:
                    throw new \Exception("Tipe mutasi '$type' tidak dikenali.");
            }

            // 3. Final Safety Check
            if ($balance->qty_available < 0 || $balance->qty_reserved < 0 || $balance->qty_in_transit < 0) {
                throw new \Exception('Transaksi ditolak: Perubahan ini akan menyebabkan saldo stok menjadi negatif.');
            }

            // 4. Hitung qty_after untuk audit ledger
            $qtyAfter = $balance->qty_available;

            // 5. Catat ke StockMutation (Ledger)
            Rst_StockMutation::create([
                'item_id' => $itemId,
                'location_id' => $locationId,
                'uom_id' => $uomId,
                'type' => $type,
                'reference_number' => $referenceNumber,
                'qty' => $qty,
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'user_id' => $userId,
                'notes' => $notes,
            ]);

            // 6. Simpan Perubahan Saldo
            $balance->save();

            return $balance;
        });
    }
}
