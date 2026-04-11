<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_RequestActivity;
use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
use App\Models\Holdings\Resto\Movement\Rst_MovementItem;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    /**
     * Tahap 1: Sous Chef Request barang dari Gudang ke Dapur
     * multiple items dalam 1 movement
     * Gudang: qty_available(-qty), qty_reserved(+qty) per item
     */
    public static function createMovement(
        int $fromLocationId,
        int $toLocationId,
        array $items, // [{item_id, qty, notes}, ...]
        ?string $notes = null
    ): Rst_Movement {
        return DB::transaction(function () use ($fromLocationId, $toLocationId, $items, $notes) {
            $movement = Rst_Movement::create([
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'type' => 'internal_transfer',
                'status' => 'requested',
                'remark' => $notes,
            ]);

            $totalItemsRequested = 0;

            foreach ($items as $itemData) {
                $itemId = $itemData['item_id'];
                $qty = $itemData['qty'];
                $itemNotes = $itemData['notes'] ?? null;

                $item = Rst_MasterItem::findOrFail($itemId);

                Rst_MovementItem::create([
                    'movement_id' => $movement->id,
                    'item_id' => $itemId,
                    'uom_id' => $item->uom_id,
                    'qty' => $qty,
                    'remark' => $itemNotes,
                ]);

                $balance = Rst_StockBalance::where('item_id', $itemId)
                    ->where('location_id', $fromLocationId)
                    ->first();

                if (! $balance) {
                    throw new \Exception("Stok tidak tersedia di lokasi asal untuk item {$item->name}.");
                }

                if ($balance->qty_available < $qty) {
                    throw new \Exception("Stok Available tidak cukup untuk item {$item->name}. Tersedia: {$balance->qty_available}");
                }

                $balance->qty_available -= $qty;
                $balance->qty_reserved += $qty;
                $balance->save();

                $totalItemsRequested++;
            }

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'requested',
                'status_from' => null,
                'status_to' => 'PENDING',
                'comment' => "Requested {$totalItemsRequested} item(s) by Sous Chef",
                'changes' => json_encode(['item_count' => $totalItemsRequested]),
            ]);

            return $movement;
        });
    }

    /**
     * Tahap 2: Exc. Chef Revise Qty (ubah qty request per item)
     * Gudang: qty_available(+selisih), qty_reserved(-selisih)
     */
    public static function reviseMovement(
        int $movementId,
        int $itemId,
        float $newQty,
        ?string $notes = null
    ): Rst_Movement {
        return DB::transaction(function () use ($movementId, $itemId, $newQty) {
            $movement = Rst_Movement::findOrFail($movementId);
            $movementItem = Rst_MovementItem::where('movement_id', $movementId)
                ->where('item_id', $itemId)
                ->firstOrFail();

            $oldQty = $movementItem->qty;
            $fromLocationId = $movement->from_location_id;

            $balance = Rst_StockBalance::where('item_id', $itemId)
                ->where('location_id', $fromLocationId)
                ->first();

            if ($newQty > $oldQty) {
                $diff = $newQty - $oldQty;
                if (! $balance || $balance->qty_available < $diff) {
                    throw new \Exception('Stok Available tidak cukup untuk increase.');
                }
                $balance->qty_available -= $diff;
                $balance->qty_reserved += $diff;
            } elseif ($newQty < $oldQty) {
                $diff = $oldQty - $newQty;
                if ($balance) {
                    $balance->qty_reserved -= $diff;
                    $balance->qty_available += $diff;
                    $balance->save();
                }
            }

            $movementItem->qty = $newQty;
            $movementItem->save();

            $item = Rst_MasterItem::findOrFail($itemId);

            $changeDetails = json_encode([
                'item_name' => $item->name,
                'qty' => ['from' => $oldQty, 'to' => $newQty],
            ]);

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'revised',
                'status_from' => 'PENDING',
                'status_to' => 'PENDING',
                'comment' => "Item {$item->name} revised from {$oldQty} to {$newQty} by Exec Chef",
                'changes' => $changeDetails,
            ]);

            return $movement;
        });
    }

    /**
     * Tahap 2b: Tambah item ke movement (Revise)
     * Gudang: qty_available(-qty), qty_reserved(+qty)
     */
    public static function addItemToMovement(
        int $movementId,
        int $itemId,
        float $qty,
        ?string $notes = null
    ): Rst_MovementItem {
        return DB::transaction(function () use ($movementId, $itemId, $qty, $notes) {
            $movement = Rst_Movement::findOrFail($movementId);

            if ($movement->status !== 'requested') {
                throw new \Exception('Hanya bisa menambahkan item pada status Requested.');
            }

            $existingItem = Rst_MovementItem::where('movement_id', $movementId)
                ->where('item_id', $itemId)
                ->first();

            if ($existingItem) {
                throw new \Exception('Item sudah ada dalam movement. Gunakan revise untuk mengubah qty.');
            }

            $item = Rst_MasterItem::findOrFail($itemId);
            $fromLocationId = $movement->from_location_id;

            $movementItem = Rst_MovementItem::create([
                'movement_id' => $movementId,
                'item_id' => $itemId,
                'uom_id' => $item->uom_id,
                'qty' => $qty,
                'remark' => $notes,
            ]);

            $balance = Rst_StockBalance::where('item_id', $itemId)
                ->where('location_id', $fromLocationId)
                ->first();

            if (! $balance) {
                throw new \Exception("Stok tidak tersedia di lokasi asal untuk item {$item->name}.");
            }

            if ($balance->qty_available < $qty) {
                throw new \Exception("Stok Available tidak cukup untuk item {$item->name}. Tersedia: {$balance->qty_available}");
            }

            $balance->qty_available -= $qty;
            $balance->qty_reserved += $qty;
            $balance->save();

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'revised',
                'status_from' => 'PENDING',
                'status_to' => 'PENDING',
                'comment' => "Added item {$item->name} qty {$qty} by Exec Chef",
                'changes' => json_encode([
                    'item_name' => $item->name,
                    'action' => 'added',
                    'qty' => $qty,
                ]),
            ]);

            return $movementItem;
        });
    }

    /**
     * Tahap 2c: Hapus item dari movement (Revise)
     * Gudang: qty_reserved(+qty), qty_available(+qty)
     */
    public static function removeItemFromMovement(
        int $movementId,
        int $movementItemId,
        ?string $notes = null
    ): void {
        DB::transaction(function () use ($movementId, $movementItemId) {
            $movement = Rst_Movement::findOrFail($movementId);

            if ($movement->status !== 'requested') {
                throw new \Exception('Hanya bisa menghapus item pada status Requested.');
            }

            $movementItem = Rst_MovementItem::where('movement_id', $movementId)
                ->where('id', $movementItemId)
                ->firstOrFail();

            $itemId = $movementItem->item_id;
            $qty = $movementItem->qty;
            $fromLocationId = $movement->from_location_id;

            $balance = Rst_StockBalance::where('item_id', $itemId)
                ->where('location_id', $fromLocationId)
                ->first();

            if ($balance) {
                $balance->qty_reserved -= $qty;
                $balance->qty_available += $qty;
                $balance->save();
            }

            $item = Rst_MasterItem::findOrFail($itemId);
            $movementItem->delete();

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'revised',
                'status_from' => 'PENDING',
                'status_to' => 'PENDING',
                'comment' => "Removed item {$item->name} qty {$qty} by Exec Chef",
                'changes' => json_encode([
                    'item_name' => $item->name,
                    'action' => 'removed',
                    'qty' => $qty,
                ]),
            ]);
        });
    }

    /**
     * Tahap 3: RM & SPV Approval
     * Tidak ada perubahan stock_balances
     */
    public static function approveMovement(
        int $movementId,
        ?string $notes = null
    ): Rst_Movement {
        return DB::transaction(function () use ($movementId, $notes) {
            $movement = Rst_Movement::findOrFail($movementId);
            $movementItem = Rst_MovementItem::where('movement_id', $movementId)->firstOrFail();
            $item = Rst_MasterItem::findOrFail($movementItem->item_id);

            $movement->status = 'approved';
            $movement->pic_name = 'RM/SPV';
            $movement->save();

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'approved',
                'status_from' => 'PENDING',
                'status_to' => 'APPROVED',
                'comment' => $notes ?: 'Approved by RM & SPV',
            ]);

            return $movement;
        });
    }

    /**
     * Tahap 4: Store Keeper Dispatch (Kirim barang)
     * Gudang: qty_reserved(-qty), qty_in_transit(+qty)
     * Mutation: OUT (Gudang -qty)
     */
    public static function dispatchItems(
        int $movementId,
        ?string $notes = null
    ): Rst_Movement {
        return DB::transaction(function () use ($movementId, $notes) {
            $movement = Rst_Movement::findOrFail($movementId);
            $movementItem = Rst_MovementItem::where('movement_id', $movementId)->firstOrFail();

            $itemId = $movementItem->item_id;
            $qty = $movementItem->qty;
            $fromLocationId = $movement->from_location_id;
            $toLocationId = $movement->to_location_id;

            $balance = Rst_StockBalance::where('item_id', $itemId)
                ->where('location_id', $fromLocationId)
                ->first();

            if (! $balance || $balance->qty_reserved < $qty) {
                throw new \Exception('Stok Reserved tidak cukup untuk dispatch.');
            }

            $beforeOut = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

            $balance->qty_reserved -= $qty;
            $balance->qty_in_transit += $qty;
            $balance->save();

            $item = Rst_MasterItem::findOrFail($itemId);
            $afterOut = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

            Rst_StockMutation::create([
                'item_id' => $itemId,
                'location_id' => $fromLocationId,
                'uom_id' => $item->uom_id,
                'type' => 'out',
                'qty' => $qty,
                'qty_before' => $beforeOut,
                'qty_after' => $afterOut,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'user_id' => 'SYSTEM',
                'notes' => $notes ?: "Dispatched to location {$toLocationId}",
            ]);

            $movement->status = 'in_transit';
            $movement->save();

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'distributed',
                'status_from' => 'APPROVED',
                'status_to' => 'IN_TRANSIT',
                'comment' => $notes ?: 'Items Dispatched by Store Keeper',
            ]);

            return $movement;
        });
    }

    /**
     * Tahap 5: Dapur Terima Barang
     * Gudang: qty_in_transit(-qty)
     * Dapur: qty_available(+qty)
     * Mutation: IN (Dapur +qty)
     */
    public static function receiveItems(
        int $movementId,
        ?string $notes = null
    ): Rst_Movement {
        return DB::transaction(function () use ($movementId, $notes) {
            $movement = Rst_Movement::findOrFail($movementId);
            $movementItem = Rst_MovementItem::where('movement_id', $movementId)->firstOrFail();

            $itemId = $movementItem->item_id;
            $qty = $movementItem->qty;
            $fromLocationId = $movement->from_location_id;
            $toLocationId = $movement->to_location_id;

            $gudangBalance = Rst_StockBalance::where('item_id', $itemId)
                ->where('location_id', $fromLocationId)
                ->first();

            if (! $gudangBalance || $gudangBalance->qty_in_transit < $qty) {
                throw new \Exception('Stok In-Transit tidak cukup.');
            }

            $beforeGudang = $gudangBalance->qty_available + $gudangBalance->qty_reserved + $gudangBalance->qty_in_transit;

            $gudangBalance->qty_in_transit -= $qty;
            $gudangBalance->save();

            $dapurBalance = Rst_StockBalance::firstOrCreate(
                ['item_id' => $itemId, 'location_id' => $toLocationId],
                ['uom_id' => $movementItem->uom_id, 'qty_available' => 0, 'qty_reserved' => 0, 'qty_in_transit' => 0, 'qty_waste' => 0]
            );

            $beforeDapur = $dapurBalance->qty_available + $dapurBalance->qty_reserved + $dapurBalance->qty_in_transit;

            $dapurBalance->qty_available += $qty;
            $dapurBalance->save();

            $item = Rst_MasterItem::findOrFail($itemId);
            $afterDapur = $dapurBalance->qty_available + $dapurBalance->qty_reserved + $dapurBalance->qty_in_transit;

            Rst_StockMutation::create([
                'item_id' => $itemId,
                'location_id' => $toLocationId,
                'uom_id' => $item->uom_id,
                'type' => 'in',
                'qty' => $qty,
                'qty_before' => $beforeDapur,
                'qty_after' => $afterDapur,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'user_id' => 'SYSTEM',
                'notes' => $notes ?: "Received from location {$fromLocationId}",
            ]);

            $movement->status = 'completed';
            $movement->approved_by_name = 'Sous Chef';
            $movement->save();

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'received',
                'status_from' => 'IN_TRANSIT',
                'status_to' => 'COMPLETED',
                'comment' => $notes ?: 'Received by Sous Chef',
            ]);

            return $movement;
        });
    }
}
