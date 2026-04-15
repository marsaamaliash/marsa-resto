<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Resep\Rst_StockRepack;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockRepackService
{
    /**
     * Menjalankan logika pecah satuan (Repack)
     */
    public function executeRepack($data)
    {
        return DB::connection('sccr_resto')->transaction(function () use ($data) {

            // 1. Ambil Data Balance Sumber
            $sourceBalance = Rst_StockBalance::where('item_id', $data['source_item_id'])
                ->where('location_id', $data['location_id'])
                ->lockForUpdate() // Mencegah race condition
                ->first();

            if (! $sourceBalance || $sourceBalance->qty_available < $data['qty_source_taken']) {
                throw new Exception('Stok sumber tidak mencukupi untuk dipecah.');
            }

            // 2. Simpan Header Repack
            $repack = Rst_StockRepack::create([
                'repack_number' => $this->generateRepackNumber(),
                'location_id' => $data['location_id'],
                'source_item_id' => $data['source_item_id'],
                'target_item_id' => $data['target_item_id'],
                'qty_source_taken' => $data['qty_source_taken'],
                'multiplier' => $data['multiplier'],
                'qty_target_result' => $data['qty_source_taken'] * $data['multiplier'],
                'user_id' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // 3. Kurangi Stok Sumber (Kardus)
            $qtyBeforeSource = $sourceBalance->qty_available;
            $sourceBalance->decrement('qty_available', $data['qty_source_taken']);

            Rst_StockMutation::create([
                'item_id' => $data['source_item_id'],
                'location_id' => $data['location_id'],
                'uom_id' => Rst_MasterItem::find($data['source_item_id'])->uom_id,
                'type' => 'repack_out',
                'qty' => $data['qty_source_taken'],
                'qty_before' => $qtyBeforeSource,
                'qty_after' => $sourceBalance->fresh()->qty_available,
                'user_id' => auth()->id(),
                'notes' => 'Pecah satuan ke item ID: '.$data['target_item_id'],
            ]);

            // 4. Tambah Stok Target (Botol/ML)
            try {
                $targetItem = Rst_MasterItem::find($data['target_item_id']);
                Log::info('Target item lookup', [
                    'target_item_id' => $data['target_item_id'],
                    'item_found' => $targetItem ? 'yes' : 'no',
                    'uom_id' => $targetItem?->uom_id,
                ]);

                $targetBalance = Rst_StockBalance::firstOrNew([
                    'item_id' => $data['target_item_id'],
                    'location_id' => $data['location_id'],
                ]);

                $qtyBeforeTarget = $targetBalance->exists ? $targetBalance->qty_available : 0;
                $targetBalance->uom_id = $targetItem?->uom_id;
                $targetBalance->qty_available += $repack->qty_target_result;
                $targetBalance->save();

                Log::info('Target balance saved', [
                    'saved' => 'yes',
                    'qty_available' => $targetBalance->qty_available,
                ]);

                Rst_StockMutation::create([
                    'item_id' => $data['target_item_id'],
                    'location_id' => $data['location_id'],
                    'uom_id' => $targetBalance->uom_id,
                    'type' => 'repack_in',
                    'qty' => $repack->qty_target_result,
                    'qty_before' => $qtyBeforeTarget,
                    'qty_after' => $targetBalance->qty_available,
                    'user_id' => auth()->id(),
                    'notes' => 'Hasil pecah dari item ID: '.$data['source_item_id'],
                ]);
            } catch (\Exception $e) {
                Log::error('Repack IN failed: '.$e->getMessage(), [
                    'target_item_id' => $data['target_item_id'],
                    'location_id' => $data['location_id'],
                    'repack_id' => $repack->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                throw new \Exception('Gagal simpan repack IN: '.$e->getMessage());
            }

            return $repack;
        });
    }

    private function generateRepackNumber()
    {
        return 'RPC-'.now()->format('Ymd').'-'.strtoupper(str()->random(4));
    }
}
