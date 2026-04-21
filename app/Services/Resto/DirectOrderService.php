<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Procurement\Rst_DirectOrder;
use App\Models\Holdings\Resto\Procurement\Rst_DirectOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DirectOrderService
{
    public static function getDirectOrderList(
        int $locationId,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        $query = Rst_DirectOrder::where('location_id', $locationId)
            ->with(['location', 'items.item', 'items.uom'])
            ->latest('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('do_number', 'like', "%$search%")
                    ->orWhere('purchaser_name', 'like', "%$search%");
            });
        }

        return $query->paginate($perPage);
    }

    public static function createFromInput(
        int $locationId,
        string $purchaserName,
        string $purchaseDate,
        string $paymentBy,
        $proofFile,
        array $items,
        ?string $notes = null
    ): Rst_DirectOrder {
        return DB::transaction(function () use ($locationId, $purchaserName, $purchaseDate, $paymentBy, $proofFile, $items, $notes) {
            $proofPath = $proofFile->store('do/proof', 'public');

            $do = Rst_DirectOrder::create([
                'do_number' => self::generateDirectOrderNumber(),
                'location_id' => $locationId,
                'purchaser_name' => $purchaserName,
                'purchase_date' => $purchaseDate,
                'payment_by' => $paymentBy,
                'proof_path' => $proofPath,
                'notes' => $notes,
                'status' => 'draft',
                'approval_level' => 0,
                'created_by' => auth()->user()?->username,
            ]);

            $totalAmount = 0;
            foreach ($items as $itemData) {
                $unitPrice = (float) ($itemData['unit_price'] ?? 0);
                $quantity = (float) ($itemData['quantity'] ?? 0);
                $itemTotal = $unitPrice * $quantity;
                $totalAmount += $itemTotal;

                Rst_DirectOrderItem::create([
                    'direct_order_id' => $do->id,
                    'item_id' => $itemData['item_id'],
                    'uom_id' => $itemData['uom_id'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            if ($totalAmount > 0) {
                $do->total_amount = $totalAmount;
                $do->save();
            }

            return $do;
        });
    }

    public static function submitForApproval(int $doId): Rst_DirectOrder
    {
        return DB::transaction(function () use ($doId) {
            $do = Rst_DirectOrder::findOrFail($doId);

            if ($do->status !== 'draft' && $do->status !== 'revised') {
                throw new \Exception('Direct Order harus dalam status draft atau revised.');
            }

            if (! $do->proof_path) {
                throw new \Exception('Bukti pembelian harus diupload terlebih dahulu.');
            }

            $do->status = 'pending_rm';
            $do->approval_level = 1;
            $do->updated_by = auth()->user()?->username;
            $do->save();

            return $do;
        });
    }

    public static function approveByRM(int $doId, ?string $notes = null): Rst_DirectOrder
    {
        return DB::transaction(function () use ($doId, $notes) {
            $do = Rst_DirectOrder::findOrFail($doId);

            if ($do->status !== 'pending_rm') {
                throw new \Exception('Direct Order tidak dalam status pending_rm.');
            }

            $do->status = 'pending_spv';
            $do->approval_level = 2;
            $do->rm_approved_by = auth()->user()?->id;
            $do->rm_approved_at = now();
            $do->rm_notes = $notes;
            $do->updated_by = auth()->user()?->username;
            $do->save();

            return $do;
        });
    }

    public static function approveBySPV(int $doId, ?string $notes = null): Rst_DirectOrder
    {
        return DB::transaction(function () use ($doId, $notes) {
            $do = Rst_DirectOrder::findOrFail($doId);

            if ($do->status !== 'pending_spv') {
                throw new \Exception('Direct Order tidak dalam status pending_spv.');
            }

            $do->status = 'approved';
            $do->approval_level = 3;
            $do->spv_approved_by = auth()->user()?->id;
            $do->spv_approved_at = now();
            $do->spv_notes = $notes;
            $do->updated_by = auth()->user()?->username;
            $do->save();

            return $do;
        });
    }

    public static function reject(int $doId, string $reason): Rst_DirectOrder
    {
        return DB::transaction(function () use ($doId, $reason) {
            $do = Rst_DirectOrder::findOrFail($doId);

            $do->status = 'rejected';
            $do->rejected_by = auth()->user()?->id;
            $do->rejected_at = now();
            $do->reject_reason = $reason;
            $do->rejected_at_level = $do->approval_level;
            $do->updated_by = auth()->user()?->username;
            $do->save();

            return $do;
        });
    }

    public static function requestRevision(int $doId, string $reason): Rst_DirectOrder
    {
        return DB::transaction(function () use ($doId, $reason) {
            $do = Rst_DirectOrder::findOrFail($doId);

            $currentLevel = $do->approval_level;
            $do->status = 'revised';
            $do->approval_level = 0;
            $do->revise_requested_by = auth()->user()?->id;
            $do->revise_requested_at = now();
            $do->revise_reason = $reason;
            $do->revise_requested_at_level = $currentLevel;
            $do->updated_by = auth()->user()?->username;
            $do->save();

            return $do;
        });
    }

    public static function updateItemPricing(
        int $doItemId,
        float $unitPrice,
        float $quantity
    ): Rst_DirectOrderItem {
        return DB::transaction(function () use ($doItemId, $unitPrice, $quantity) {
            $doItem = Rst_DirectOrderItem::findOrFail($doItemId);
            $do = $doItem->directOrder;

            if (! $do->canBeEdited()) {
                throw new \Exception('Direct Order tidak dapat diedit pada status ini.');
            }

            $doItem->unit_price = $unitPrice;
            $doItem->quantity = $quantity;
            $doItem->calculateTotalPrice();
            $doItem->save();

            self::recalculateTotalAmount($do);

            return $doItem;
        });
    }

    public static function recalculateTotalAmount(Rst_DirectOrder $do): void
    {
        $totalAmount = $do->items()->sum('total_price');
        $do->total_amount = $totalAmount > 0 ? $totalAmount : null;
        $do->save();
    }

    public static function updateProof(int $doId, $file): string
    {
        $do = Rst_DirectOrder::findOrFail($doId);

        if (! $do->canBeEdited()) {
            throw new \Exception('Direct Order tidak dapat diedit pada status ini.');
        }

        if ($do->proof_path && Storage::disk('public')->exists($do->proof_path)) {
            Storage::disk('public')->delete($do->proof_path);
        }

        $path = $file->store('do/proof', 'public');
        $do->proof_path = $path;
        $do->save();

        return $path;
    }

    public static function updateDODetails(
        int $doId,
        int $vendorId,
        string $paymentBy,
        ?string $notes = null
    ): Rst_DirectOrder {
        return DB::transaction(function () use ($doId, $paymentBy, $notes) {
            $do = Rst_DirectOrder::findOrFail($doId);

            if (! $do->canBeEdited()) {
                throw new \Exception('Direct Order tidak dapat diedit pada status ini.');
            }

            $do->payment_by = $paymentBy;
            $do->notes = $notes;
            $do->updated_by = auth()->user()?->username;
            $do->save();

            return $do;
        });
    }

    public static function generateDirectOrderNumber(): string
    {
        $prefix = 'DO-'.date('Ymd');
        $lastDO = Rst_DirectOrder::where('do_number', 'like', $prefix.'%')
            ->latest('id')
            ->first();

        $seq = $lastDO ? (int) substr($lastDO->do_number, -4) + 1 : 1;

        return $prefix.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
