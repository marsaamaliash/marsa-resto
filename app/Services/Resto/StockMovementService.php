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
                'reference_number' => ReferenceNumberService::generateMovementNumber(),
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

                $beforeReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                $balance->qty_available -= $qty;
                $balance->qty_reserved += $qty;
                $balance->save();

                $afterReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                Rst_StockMutation::create([
                    'item_id' => $itemId,
                    'location_id' => $fromLocationId,
                    'uom_id' => $item->uom_id,
                    'type' => 'reservation',
                    'reference_number' => $movement->reference_number,
                    'qty' => $qty,
                    'qty_before' => $beforeReserve,
                    'qty_after' => $afterReserve,
                    'from_location_id' => $fromLocationId,
                    'to_location_id' => $toLocationId,
                    'user_id' => 'SYSTEM',
                    'notes' => "Reserved for movement #{$movement->id}",
                ]);

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
                $balance->save();
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
     * Tahap 3: Multi-Level Approval (Exc Chef → RM → SPV)
     * Tidak ada perubahan stock_balances
     *
     * @param  int  $level  1=Exc Chef, 2=RM, 3=SPV
     * @param  string  $approverName  Nama approver
     *
     * @throws \Exception
     */
    public static function approveMovement(
        int $movementId,
        int $level,
        string $approverName,
        ?string $notes = null
    ): Rst_Movement {
        return DB::transaction(function () use ($movementId, $level, $approverName, $notes) {
            $movement = Rst_Movement::findOrFail($movementId);

            if ($movement->status !== 'requested') {
                throw new \Exception('Hanya bisa approve pada status Requested.');
            }

            $currentLevel = $movement->approval_level ?? 0;

            if ($level !== $currentLevel + 1) {
                throw new \Exception('Approval level tidak valid. Selesaikan approval sebelumnya.');
            }

            $now = now();

            match ($level) {
                1 => $movement->fill([
                    'approval_level' => 1,
                    'exc_chef_approved_by' => $approverName,
                    'exc_chef_approved_at' => $now,
                ]),
                2 => $movement->fill([
                    'approval_level' => 2,
                    'rm_approved_by' => $approverName,
                    'rm_approved_at' => $now,
                ]),
                3 => $movement->fill([
                    'approval_level' => 3,
                    'spv_approved_by' => $approverName,
                    'spv_approved_at' => $now,
                    'status' => 'approved',
                ]),
                default => throw new \Exception('Level approval tidak valid.'),
            };

            $movement->save();

            $actionName = match ($level) {
                1 => 'approved_exc_chef',
                2 => 'approved_rm',
                3 => 'approved_spv',
                default => 'approved',
            };

            $statusTo = match ($level) {
                1 => 'PENDING (Exc Chef Approved)',
                2 => 'PENDING (RM Approved)',
                3 => 'APPROVED',
                default => 'APPROVED',
            };

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => $approverName,
                'action' => $actionName,
                'status_from' => 'REQUESTED',
                'status_to' => $statusTo,
                'comment' => $notes ?: "Approved by {$approverName}",
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
            $movementItems = Rst_MovementItem::where('movement_id', $movementId)->get();

            $fromLocationId = $movement->from_location_id;
            $toLocationId = $movement->to_location_id;
            $totalDispatched = 0;

            foreach ($movementItems as $movementItem) {
                $itemId = $movementItem->item_id;
                $qty = $movementItem->qty;

                $balance = Rst_StockBalance::where('item_id', $itemId)
                    ->where('location_id', $fromLocationId)
                    ->first();

                if (! $balance || $balance->qty_reserved < $qty) {
                    $itemName = $movementItem->item?->name;
                    $reserved = $balance ? $balance->qty_reserved : 0;
                    throw new \Exception("Stok Reserved tidak cukup untuk {$itemName}. Tersedia: {$reserved}");
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
                    'reference_number' => $movement->reference_number,
                    'qty' => $qty,
                    'qty_before' => $beforeOut,
                    'qty_after' => $afterOut,
                    'from_location_id' => $fromLocationId,
                    'to_location_id' => $toLocationId,
                    'user_id' => 'SYSTEM',
                    'notes' => $notes ?: "Dispatched to location {$toLocationId}",
                ]);

                $totalDispatched++;
            }

            $movement->status = 'in_transit';
            $movement->save();

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'distributed',
                'status_from' => 'APPROVED',
                'status_to' => 'IN_TRANSIT',
                'comment' => $notes ?: "Items Dispatched by Store Keeper ({$totalDispatched} item(s))",
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
            $movementItems = Rst_MovementItem::where('movement_id', $movementId)->get();

            $fromLocationId = $movement->from_location_id;
            $toLocationId = $movement->to_location_id;
            $totalReceived = 0;

            foreach ($movementItems as $movementItem) {
                $itemId = $movementItem->item_id;
                $qty = $movementItem->qty;

                $gudangBalance = Rst_StockBalance::where('item_id', $itemId)
                    ->where('location_id', $fromLocationId)
                    ->first();

                if (! $gudangBalance || $gudangBalance->qty_in_transit < $qty) {
                    $itemName = $movementItem->item?->name;
                    $inTransit = $gudangBalance ? $gudangBalance->qty_in_transit : 0;
                    throw new \Exception("Stok In-Transit tidak cukup untuk {$itemName}. Tersedia: {$inTransit}");
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
                    'reference_number' => $movement->reference_number,
                    'qty' => $qty,
                    'qty_before' => $beforeDapur,
                    'qty_after' => $afterDapur,
                    'from_location_id' => $fromLocationId,
                    'to_location_id' => $toLocationId,
                    'user_id' => 'SYSTEM',
                    'notes' => $notes ?: "Received from location {$fromLocationId}",
                ]);

                $totalReceived++;
            }

            $movement->status = 'completed';
            $movement->approved_by_name = 'Sous Chef';
            $movement->save();

            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => 'SYSTEM',
                'action' => 'received',
                'status_from' => 'IN_TRANSIT',
                'status_to' => 'COMPLETED',
                'comment' => $notes ?: "Received by Sous Chef ({$totalReceived} item(s))",
            ]);

            return $movement;
        });
    }

    /**
     * Reject Movement - Unreserve Stock
     * Gudang: qty_reserved(-qty), qty_available(+qty)
     * Applicable untuk semua level approval (selama belum dispatch)
     */
    public static function rejectMovement(
        int $movementId,
        string $rejecterName,
        ?string $notes = null
    ): Rst_Movement {
        return DB::transaction(function () use ($movementId, $rejecterName, $notes) {
            $movement = Rst_Movement::findOrFail($movementId);

            if (! in_array($movement->status, ['requested', 'approved'])) {
                throw new \Exception('Hanya bisa reject pada status Requested atau Approved.');
            }

            $movementItems = Rst_MovementItem::where('movement_id', $movementId)->get();

            foreach ($movementItems as $movementItem) {
                $itemId = $movementItem->item_id;
                $qty = $movementItem->qty;
                $fromLocationId = $movement->from_location_id;
                $toLocationId = $movement->to_location_id;

                $balance = Rst_StockBalance::where('item_id', $itemId)
                    ->where('location_id', $fromLocationId)
                    ->first();

                if ($balance && $balance->qty_reserved >= $qty) {
                    $beforeUnreserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                    $balance->qty_reserved -= $qty;
                    $balance->qty_available += $qty;
                    $balance->save();

                    $afterUnreserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                    $item = Rst_MasterItem::findOrFail($itemId);

                    Rst_StockMutation::create([
                        'item_id' => $itemId,
                        'location_id' => $fromLocationId,
                        'uom_id' => $item->uom_id,
                        'type' => 'unreserved',
                        'reference_number' => $movement->reference_number,
                        'qty' => $qty,
                        'qty_before' => $beforeUnreserve,
                        'qty_after' => $afterUnreserve,
                        'from_location_id' => $fromLocationId,
                        'to_location_id' => $toLocationId,
                        'user_id' => 'SYSTEM',
                        'notes' => "Unreserved for movement #{$movement->id} - Rejected by {$rejecterName}",
                    ]);
                }
            }

            $statusFrom = $movement->status === 'approved' ? 'APPROVED' : 'REQUESTED';
            $movement->status = 'rejected';
            $movement->save();

            $comment = $notes ?: "Rejected by {$rejecterName}";
            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => $rejecterName,
                'action' => 'rejected',
                'status_from' => $statusFrom,
                'status_to' => 'REJECTED',
                'comment' => $comment,
            ]);

            return $movement;
        });
    }

    /**
     * Reject Dispatch - barang rusak/salah saat pengiriman
     * Gudang: qty_reserved(-qty), TIDAK mengembalikan ke available
     * Mutation: WASTE (barang dibuang)
     * Status: CLOSED
     */
    public static function rejectDispatch(
        int $movementId,
        string $rejecterName,
        ?string $notes = null
    ): Rst_Movement {
        return DB::transaction(function () use ($movementId, $rejecterName, $notes) {
            $movement = Rst_Movement::findOrFail($movementId);

            if ($movement->status !== 'in_transit') {
                throw new \Exception('Hanya bisa reject dispatch pada status In Transit.');
            }

            $movementItems = Rst_MovementItem::where('movement_id', $movementId)->get();

            $toLocationId = $movement->to_location_id;
            $fromLocationId = $movement->from_location_id;
            $totalRejected = 0;

            foreach ($movementItems as $movementItem) {
                $itemId = $movementItem->item_id;
                $qty = $movementItem->qty;

                $balance = Rst_StockBalance::where('item_id', $itemId)
                    ->where('location_id', $fromLocationId)
                    ->first();

                if ($balance && $balance->qty_reserved >= $qty) {
                    $before = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                    $balance->qty_reserved -= $qty;
                    $balance->qty_in_transit -= $qty;
                    $balance->save();

                    $after = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                    $item = Rst_MasterItem::findOrFail($itemId);

                    Rst_StockMutation::create([
                        'item_id' => $itemId,
                        'location_id' => $fromLocationId,
                        'uom_id' => $item->uom_id,
                        'type' => 'waste',
                        'reference_number' => $movement->reference_number,
                        'qty' => $qty,
                        'qty_before' => $before,
                        'qty_after' => $after,
                        'from_location_id' => $fromLocationId,
                        'to_location_id' => $toLocationId,
                        'user_id' => 'SYSTEM',
                        'notes' => $notes ?: 'Item damaged/wasted during dispatch',
                    ]);
                }

                $totalRejected++;
            }

            $movement->status = 'closed';
            $movement->save();

            $comment = $notes ?: 'Failed to dispatch: Item damaged';
            Rst_RequestActivity::create([
                'movement_id' => $movement->id,
                'pic' => $rejecterName,
                'action' => 'dispatch_rejected',
                'status_from' => 'IN_TRANSIT',
                'status_to' => 'CLOSED',
                'comment' => $comment,
            ]);

            return $movement;
        });
    }
}
