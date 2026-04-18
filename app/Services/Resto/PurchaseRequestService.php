<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseRequest;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseRequestItem;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
{
    /**
     * Get critical stock items (qty <= min_stock) for a location
     * Uses same logic as StockMinimalTable for consistency
     *
     * @return array<int, array{item: Rst_MasterItem, balance: Rst_StockBalance, deficit: float, status: string}>
     */
    public static function getCriticalStockItems(int $locationId): array
    {
        $balances = Rst_StockBalance::with('item')
            ->where('location_id', $locationId)
            ->whereHas('item', function ($query) {
                $query->whereColumn('stock_balances.qty_available', '<=', 'items.min_stock')
                    ->where('items.min_stock', '>', 0);
            })
            ->get();

        $criticalItems = [];

        foreach ($balances as $balance) {
            $item = $balance->item;
            if (! $item) {
                continue;
            }

            $qtyAvailable = $balance->qty_available;
            $minStock = $item->min_stock;

            if ($minStock <= 0 || $qtyAvailable > $minStock * 1.2) {
                continue;
            }

            $status = self::getStockStatus($qtyAvailable, $minStock);
            $deficit = $minStock - $qtyAvailable;

            $criticalItems[] = [
                'item' => $item,
                'balance' => $balance,
                'deficit' => $deficit,
                'min_stock' => $minStock,
                'actual_stock' => $qtyAvailable,
                'status' => $status,
            ];
        }

        return $criticalItems;
    }

    /**
     * Determine stock status (critical or warning)
     * Same logic as StockMinimalTable
     */
    private static function getStockStatus(float $qtyAvailable, float $minStock): string
    {
        if ($qtyAvailable <= $minStock) {
            return 'critical';
        }
        if ($qtyAvailable <= $minStock * 1.2) {
            return 'warning';
        }

        return 'normal';
    }

    /**
     * Create a new Purchase Request from critical stock items
     *
     * @param  array<int, array{item_id: int, qty: float, notes: ?string, is_critical?: bool}>  $items
     */
    public static function createFromCritical(
        int $locationId,
        array $items,
        ?string $notes = null,
        ?string $requesterName = null,
        ?string $requiredDate = null
    ): Rst_PurchaseRequest {
        return DB::transaction(function () use ($locationId, $items, $notes, $requesterName, $requiredDate) {
            $pr = Rst_PurchaseRequest::create([
                'pr_number' => ReferenceNumberService::generatePurchaseRequestNumber(),
                'requester_location_id' => $locationId,
                'status' => 'draft',
                'approval_level' => 0,
                'notes' => $notes,
                'requested_by' => $requesterName,
                'requested_at' => now(),
                'required_date' => $requiredDate,
                'created_by' => auth()->user()?->id,
            ]);

            $totalCost = 0;

            foreach ($items as $itemData) {
                $item = Rst_MasterItem::findOrFail($itemData['item_id']);
                $balance = Rst_StockBalance::where('item_id', $itemData['item_id'])
                    ->where('location_id', $locationId)
                    ->first();

                $unitCost = $item->last_purchase_price ?? $item->default_cost ?? 0;
                $qty = $itemData['qty'];
                $itemTotal = $unitCost * $qty;
                $isCritical = $itemData['is_critical'] ?? true;

                Rst_PurchaseRequestItem::create([
                    'purchase_request_id' => $pr->id,
                    'item_id' => $itemData['item_id'],
                    'requested_qty' => $qty,
                    'uom_id' => $item->uom_id,
                    'unit_cost' => $unitCost > 0 ? $unitCost : null,
                    'total_cost' => $itemTotal > 0 ? $itemTotal : null,
                    'is_critical' => $isCritical,
                    'actual_stock' => $balance?->qty_available ?? 0,
                    'min_stock' => $item->min_stock ?? 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalCost += $itemTotal;
            }

            if ($totalCost > 0) {
                $pr->total_estimated_cost = $totalCost;
                $pr->save();
            }

            return $pr;
        });
    }

    /**
     * Add item to existing PR (for non-critical items)
     */
    public static function addItemToPR(
        int $prId,
        int $itemId,
        float $qty,
        ?string $notes = null
    ): Rst_PurchaseRequestItem {
        return DB::transaction(function () use ($prId, $itemId, $qty, $notes) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! $pr->canBeEdited()) {
                throw new \Exception('Purchase Request tidak dapat diedit pada status ini.');
            }

            $existingItem = Rst_PurchaseRequestItem::where('purchase_request_id', $prId)
                ->where('item_id', $itemId)
                ->first();

            if ($existingItem) {
                throw new \Exception('Item sudah ada dalam Purchase Request. Gunakan update untuk mengubah qty.');
            }

            $item = Rst_MasterItem::findOrFail($itemId);
            $unitCost = $item->last_purchase_price ?? $item->default_cost ?? 0;
            $itemTotal = $unitCost * $qty;

            $prItem = Rst_PurchaseRequestItem::create([
                'purchase_request_id' => $prId,
                'item_id' => $itemId,
                'requested_qty' => $qty,
                'uom_id' => $item->uom_id,
                'unit_cost' => $unitCost > 0 ? $unitCost : null,
                'total_cost' => $itemTotal > 0 ? $itemTotal : null,
                'is_critical' => false,
                'actual_stock' => 0,
                'min_stock' => $item->min_stock ?? 0,
                'notes' => $notes,
            ]);

            self::recalculateTotalCost($pr);

            return $prItem;
        });
    }

    /**
     * Update item qty in PR
     */
    public static function updateItemQty(
        int $prItemId,
        float $newQty
    ): Rst_PurchaseRequestItem {
        return DB::transaction(function () use ($prItemId, $newQty) {
            $prItem = Rst_PurchaseRequestItem::findOrFail($prItemId);
            $pr = $prItem->purchaseRequest;

            if (! $pr->canBeEdited()) {
                throw new \Exception('Purchase Request tidak dapat diedit pada status ini.');
            }

            $prItem->requested_qty = $newQty;

            if ($prItem->unit_cost !== null) {
                $prItem->total_cost = $newQty * $prItem->unit_cost;
            }

            $prItem->save();

            self::recalculateTotalCost($pr);

            return $prItem;
        });
    }

    /**
     * Remove item from PR
     */
    public static function removeItemFromPR(int $prItemId): void
    {
        DB::transaction(function () use ($prItemId) {
            $prItem = Rst_PurchaseRequestItem::findOrFail($prItemId);
            $pr = $prItem->purchaseRequest;

            if (! $pr->canBeEdited()) {
                throw new \Exception('Purchase Request tidak dapat diedit pada status ini.');
            }

            $prItem->delete();

            self::recalculateTotalCost($pr);
        });
    }

    /**
     * Submit PR to RM for approval
     */
    public static function submitToRM(
        int $prId,
        ?string $notes = null,
        ?string $requesterName = null,
        ?string $requiredDate = null // 1. Tambahkan parameter ini
    ): Rst_PurchaseRequest {
        return DB::transaction(function () use ($prId, $notes, $requesterName, $requiredDate) { // 2. Jangan lupa di-use
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! $pr->isDraft() && ! $pr->isRevised()) {
                throw new \Exception('Hanya PR dengan status Draft atau Revised yang dapat disubmit.');
            }

            $itemCount = $pr->items()->count();
            if ($itemCount === 0) {
                throw new \Exception('PR harus memiliki minimal 1 item.');
            }

            $pr->fill([
                'status' => 'pending_rm',
                'approval_level' => 1,
                'notes' => $notes ?? $pr->notes,
                'requested_by' => $requesterName ?? $pr->requested_by,
                'requested_at' => now(),
                // 3. Gunakan $requiredDate dari inputan, kalau kosong baru pakai dari database/default
                'required_date' => $requiredDate ?? $pr->required_date ?? now()->addDays(7),
            ]);
            $pr->save();

            return $pr;
        });
    }

    /**
     * RM Approves PR - move to SPV approval
     */
    public static function approveByRM(
        int $prId,
        ?string $notes = null,
        ?string $approverName = null
    ): Rst_PurchaseRequest {
        return DB::transaction(function () use ($prId, $notes, $approverName) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! $pr->isPendingRM()) {
                throw new \Exception('PR tidak dalam status pending RM approval.');
            }

            $pr->fill([
                'status' => 'pending_spv',
                'approval_level' => 2,
                'rm_approved_by' => $approverName,
                'rm_approved_at' => now(),
                'rm_notes' => $notes,
            ]);
            $pr->save();

            return $pr;
        });
    }

    /**
     * SPV Approves PR - final approval
     */
    public static function approveBySPV(
        int $prId,
        ?string $notes = null,
        ?string $approverName = null
    ): Rst_PurchaseRequest {
        return DB::transaction(function () use ($prId, $notes, $approverName) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! $pr->isPendingSPV()) {
                throw new \Exception('PR tidak dalam status pending SPV approval.');
            }

            $pr->fill([
                'status' => 'approved',
                'approval_level' => 3,
                'spv_approved_by' => $approverName,
                'spv_approved_at' => now(),
                'spv_notes' => $notes,
            ]);
            $pr->save();

            return $pr;
        });
    }

    /**
     * Reject PR - goes back to store keeper with reason
     * Can be rejected at any approval level
     */
    public static function reject(
        int $prId,
        string $reason,
        int $atLevel,
        ?string $rejecterName = null
    ): Rst_PurchaseRequest {
        return DB::transaction(function () use ($prId, $reason, $atLevel, $rejecterName) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! in_array($pr->status, ['pending_rm', 'pending_spv'])) {
                throw new \Exception('Hanya PR yang pending approval yang dapat direject.');
            }

            if (empty($reason)) {
                throw new \Exception('Alasan reject wajib diisi.');
            }

            $pr->fill([
                'status' => 'rejected',
                'approval_level' => 0,
                'rejected_by' => $rejecterName,
                'rejected_at' => now(),
                'reject_reason' => $reason,
                'rejected_at_level' => $atLevel,
            ]);
            $pr->save();

            return $pr;
        });
    }

    /**
     * Request Revise - RM/SPV can request revise back to store keeper
     */
    public static function requestRevise(
        int $prId,
        string $reason,
        int $atLevel,
        ?string $requesterName = null
    ): Rst_PurchaseRequest {
        return DB::transaction(function () use ($prId, $reason, $atLevel, $requesterName) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! in_array($pr->status, ['pending_rm', 'pending_spv'])) {
                throw new \Exception('Hanya PR yang pending approval yang dapat direquest revise.');
            }

            if (empty($reason)) {
                throw new \Exception('Alasan revise wajib diisi.');
            }

            $pr->fill([
                'status' => 'revised',
                'approval_level' => 0,
                'revise_requested_by' => $requesterName,
                'revise_requested_at' => now(),
                'revise_reason' => $reason,
                'revise_requested_at_level' => $atLevel,
            ]);
            $pr->save();

            return $pr;
        });
    }

    /**
     * Revise PR - store keeper updates items after revise request
     *
     * @param  array<int, array{id: ?int, item_id: int, qty: float, notes: ?string, is_critical?: bool}>  $items
     */
    public static function revisePR(
        int $prId,
        array $items,
        ?string $notes = null,
        ?string $requiredDate = null
    ): Rst_PurchaseRequest {
        return DB::transaction(function () use ($prId, $items, $notes, $requiredDate) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! $pr->isRevised()) {
                throw new \Exception('Hanya PR dengan status Revised yang dapat direvisi.');
            }

            $pr->items()->delete();

            $totalCost = 0;
            $locationId = $pr->requester_location_id;

            foreach ($items as $itemData) {
                $item = Rst_MasterItem::findOrFail($itemData['item_id']);
                $balance = Rst_StockBalance::where('item_id', $itemData['item_id'])
                    ->where('location_id', $locationId)
                    ->first();

                $unitCost = $item->last_purchase_price ?? $item->default_cost ?? 0;
                $qty = $itemData['qty'];
                $itemTotal = $unitCost * $qty;
                $isCritical = $itemData['is_critical'] ?? false;

                Rst_PurchaseRequestItem::create([
                    'purchase_request_id' => $prId,
                    'item_id' => $itemData['item_id'],
                    'requested_qty' => $qty,
                    'uom_id' => $item->uom_id,
                    'unit_cost' => $unitCost > 0 ? $unitCost : null,
                    'total_cost' => $itemTotal > 0 ? $itemTotal : null,
                    'is_critical' => $isCritical,
                    'actual_stock' => $balance?->qty_available ?? 0,
                    'min_stock' => $item->min_stock ?? 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalCost += $itemTotal;
            }

            $pr->fill([
                'status' => 'pending_rm',
                'approval_level' => 1,
                'notes' => $notes ?? $pr->notes,
                'required_date' => $requiredDate ?? $pr->required_date,
                'total_estimated_cost' => $totalCost > 0 ? $totalCost : 0,
            ]);
            $pr->save();

            return $pr;
        });
    }

    /**
     * Update PR items while keeping status unchanged (draft or revised)
     *
     * @param  array<int, array{id: ?int, item_id: int, qty: float, notes: ?string}>  $items
     */
    public static function updatePRItems(
        int $prId,
        array $items,
        ?string $notes = null,
        ?string $requiredDate = null
    ): Rst_PurchaseRequest {
        return DB::transaction(function () use ($prId, $items, $notes, $requiredDate) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! $pr->canBeEdited()) {
                throw new \Exception('Purchase Request tidak dapat diedit pada status ini.');
            }

            $pr->items()->delete();

            $totalCost = 0;
            $locationId = $pr->requester_location_id;

            foreach ($items as $itemData) {
                $item = Rst_MasterItem::findOrFail($itemData['item_id']);
                $balance = Rst_StockBalance::where('item_id', $itemData['item_id'])
                    ->where('location_id', $locationId)
                    ->first();

                $unitCost = $item->last_purchase_price ?? $item->default_cost ?? 0;
                $qty = $itemData['qty'];
                $itemTotal = $unitCost * $qty;
                $isCritical = $itemData['is_critical'] ?? false;

                Rst_PurchaseRequestItem::create([
                    'purchase_request_id' => $prId,
                    'item_id' => $itemData['item_id'],
                    'requested_qty' => $qty,
                    'uom_id' => $item->uom_id,
                    'unit_cost' => $unitCost > 0 ? $unitCost : null,
                    'total_cost' => $itemTotal > 0 ? $itemTotal : null,
                    'is_critical' => $isCritical,
                    'actual_stock' => $balance?->qty_available ?? 0,
                    'min_stock' => $item->min_stock ?? 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);

                $totalCost += $itemTotal;
            }

            $pr->fill([
                'notes' => $notes ?? $pr->notes,
                'required_date' => $requiredDate ?? $pr->required_date,
                'total_estimated_cost' => $totalCost > 0 ? $totalCost : 0,
            ]);
            $pr->save();

            return $pr;
        });
    }

    /**
     * Recalculate total cost after item changes
     */
    private static function recalculateTotalCost(Rst_PurchaseRequest $pr): void
    {
        $total = $pr->items()->sum('total_cost');
        $pr->total_estimated_cost = $total ?? 0;
        $pr->save();
    }

    /**
     * Check if PR can be deleted
     */
    public static function canDelete(Rst_PurchaseRequest $pr): bool
    {
        return in_array($pr->status, ['draft', 'rejected', 'revised']);
    }

    /**
     * Delete PR and its items
     */
    public static function deletePR(int $prId): void
    {
        DB::transaction(function () use ($prId) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if (! self::canDelete($pr)) {
                throw new \Exception('Hanya PR dengan status Draft, Rejected, atau Revised yang dapat dihapus.');
            }

            $pr->items()->delete();
            $pr->delete();
        });
    }

    /**
     * Update PR notes
     */
    public static function updateNotes(
        int $prId,
        ?string $notes = null
    ): Rst_PurchaseRequest {
        $pr = Rst_PurchaseRequest::findOrFail($prId);

        if (! $pr->canBeEdited()) {
            throw new \Exception('Purchase Request tidak dapat diedit pada status ini.');
        }

        $pr->notes = $notes;
        $pr->save();

        return $pr;
    }
}
