<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Single Source of Truth untuk semua mutasi stok (Movement, Procurement, Production)
     */
    public static function addMutation(
        $itemId,
        $locationId,
        $uomId,
        $qty,
        $type, // in, out, transfer_in, transfer_out, reserve, unreserve, consume, waste, clear_transit
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
                case 'transfer_in': // Barang masuk ke lokasi tujuan (Dapur)
                    $balance->qty_available += $qty;
                    break;

                case 'reserve': // Mengunci stok (RM Approval / Start Production)
                    if ($balance->qty_available < $qty) {
                        throw new \Exception('Gagal Reserve! Stok Available tidak cukup.');
                    }
                    $balance->qty_available -= $qty;
                    $balance->qty_reserved += $qty;
                    break;

                case 'unreserve': // Batal Movement / Batal Masak
                    if ($balance->qty_reserved < $qty) {
                        throw new \Exception('Gagal Unreserve! Stok Reserved tidak cukup.');
                    }
                    $balance->qty_reserved -= $qty;
                    $balance->qty_available += $qty;
                    break;

                case 'transfer_out': // Admin Gudang klik "Kirim"
                    if ($balance->qty_reserved < $qty) {
                        throw new \Exception('Gagal Transfer Out! Stok Reserved tidak cukup.');
                    }
                    $balance->qty_reserved -= $qty;
                    $balance->qty_in_transit += $qty;
                    break;

                case 'clear_transit': // Membersihkan in_transit di lokasi asal saat barang sudah sampai
                    if ($balance->qty_in_transit < $qty) {
                        throw new \Exception('Gagal Clear Transit! Stok In-Transit tidak mencukupi.');
                    }
                    $balance->qty_in_transit -= $qty;
                    break;

                case 'consume': // Digunakan dalam Production (bahan baku habis)
                    if ($balance->qty_reserved < $qty) {
                        throw new \Exception('Gagal Consume! Stok Reserved tidak mencukupi.');
                    }
                    $balance->qty_reserved -= $qty;
                    break;

                case 'waste': // Pencatatan barang rusak/basi
                    if ($balance->qty_available < $qty) {
                        throw new \Exception('Gagal Waste! Stok Available tidak mencukupi.');
                    }
                    $balance->qty_available -= $qty;
                    $balance->qty_waste += $qty;
                    break;

                case 'adjustment': // Stock Opname
                    $balance->qty_available += $qty; // Qty bisa negatif
                    break;

                default:
                    throw new \Exception("Tipe mutasi '$type' tidak dikenali.");
            }

            // 3. Final Safety Check
            if ($balance->qty_available < 0 || $balance->qty_reserved < 0 || $balance->qty_in_transit < 0) {
                throw new \Exception('Transaksi ditolak: Perubahan ini akan menyebabkan saldo stok menjadi negatif.');
            }

            // 4. Catat ke StockMutation (Ledger)
            Rst_StockMutation::create([
                'item_id' => $itemId,
                'location_id' => $locationId,
                'uom_id' => $uomId,
                'type' => $type,
                'qty' => $qty,
                'qty_before' => $qtyBefore,
                'qty_after' => $balance->qty_available, // Menunjukkan stok yang bisa dipakai setelah mutasi
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'notes' => $notes,
            ]);

            // 5. Simpan Perubahan Saldo
            $balance->save();

            return $balance;
        });
    }
}
