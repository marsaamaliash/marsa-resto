<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Master\Rst_MasterVendor;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrder;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrderItem;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderService
{
    /**
     * Get approved purchase requests for a location
     */
    public static function getApprovedPurchaseRequests(int $locationId): array
    {
        return Rst_PurchaseRequest::where('requester_location_id', $locationId)
            ->where('status', 'approved')
            ->whereDoesntHave('purchaseOrders', function ($query) {
                $query->whereNotIn('status', ['rejected']);
            })
            ->with('items.item', 'items.uom')
            ->get()
            ->toArray();
    }

    /**
     * Create a new Purchase Order from Purchase Request
     */
    public static function createFromPurchaseRequest(
        int $prId,
        string $vendorName,
        ?int $vendorId = null,
        string $paymentBy = 'holding',
        ?string $quotationPath = null,
        ?string $vendorNotes = null,
        array $itemPrices = []
    ): Rst_PurchaseOrder {
        return DB::transaction(function () use ($prId, $vendorName, $vendorId, $paymentBy, $quotationPath, $vendorNotes, $itemPrices) {
            $pr = Rst_PurchaseRequest::findOrFail($prId);

            if ($pr->status !== 'approved') {
                throw new \Exception('Purchase Request harus dalam status approved.');
            }

            $po = Rst_PurchaseOrder::create([
                'po_number' => self::generatePurchaseOrderNumber(),
                'purchase_request_id' => $prId,
                'vendor_id' => $vendorId,
                'vendor_name' => $vendorName,
                'location_id' => $pr->requester_location_id,
                'payment_by' => $paymentBy,
                'quotation_path' => $quotationPath,
                'notes' => $vendorNotes,
                'status' => 'draft',
                'approval_level' => 0,
                'created_by' => auth()->user()?->username,
            ]);

            // Copy items from PR to PO with user-input prices
            $totalAmount = 0;
            foreach ($pr->items as $index => $prItem) {
                $unitPrice = $itemPrices[$index] ?? ($prItem->unit_cost ?? 0);
                $itemTotal = $unitPrice * $prItem->requested_qty;
                $totalAmount += $itemTotal;

                Rst_PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $prItem->item_id,
                    'uom_id' => $prItem->uom_id,
                    'ordered_qty' => $prItem->requested_qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $itemTotal,
                    'notes' => $prItem->notes,
                ]);
            }

            if ($totalAmount > 0) {
                $po->total_amount = $totalAmount;
                $po->save();
            }

            return $po;
        });
    }

    /**
     * Create new vendor
     */
    public static function createVendor(
        string $name,
        string $code,
        string $noTelp,
        string $address
    ): Rst_MasterVendor {
        return Rst_MasterVendor::create([
            'name' => $name,
            'code' => $code,
            'no_telp' => $noTelp,
            'address' => $address,
            'is_active' => true,
        ]);
    }

    /**
     * Get all active vendors
     */
    public static function getActiveVendors(): array
    {
        return Rst_MasterVendor::where('is_active', true)
            ->get()
            ->toArray();
    }

    /**
     * Update PO details (location, PR, vendor, payment)
     */
    public static function updatePODetails(
        int $poId,
        int $vendorId,
        string $paymentBy,
        ?string $notes = null
    ): Rst_PurchaseOrder {
        return DB::transaction(function () use ($poId, $vendorId, $paymentBy, $notes) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            if (! $po->canBeEdited()) {
                throw new \Exception('Purchase Order tidak dapat diedit pada status ini.');
            }

            $vendor = Rst_MasterVendor::findOrFail($vendorId);
            if (! $vendor->is_active) {
                throw new \Exception('Vendor tidak aktif.');
            }

            $po->vendor_id = $vendorId;
            $po->vendor_name = $vendor->name;
            $po->payment_by = $paymentBy;
            $po->notes = $notes;
            $po->updated_by = auth()->user()?->username;
            $po->save();

            return $po;
        });
    }

    /**
     * Update PO items with pricing
     */
    public static function updateItemPricing(
        int $poItemId,
        float $unitPrice,
        float $quantity
    ): Rst_PurchaseOrderItem {
        return DB::transaction(function () use ($poItemId, $unitPrice, $quantity) {
            $poItem = Rst_PurchaseOrderItem::findOrFail($poItemId);
            $po = $poItem->purchaseOrder;

            if (! $po->canBeEdited()) {
                throw new \Exception('Purchase Order tidak dapat diedit pada status ini.');
            }

            $poItem->unit_price = $unitPrice;
            $poItem->ordered_qty = $quantity;
            $poItem->calculateTotalPrice();
            $poItem->save();

            self::recalculateTotalAmount($po);

            return $poItem;
        });
    }

    /**
     * Recalculate total amount
     */
    public static function recalculateTotalAmount(Rst_PurchaseOrder $po): void
    {
        $totalAmount = $po->items()->sum('total_price');
        $po->total_amount = $totalAmount > 0 ? $totalAmount : null;
        $po->save();
    }

    /**
     * Update quotation file
     */
    public static function updateQuotation(int $poId, $file): string
    {
        $po = Rst_PurchaseOrder::findOrFail($poId);

        if (! $po->canBeEdited()) {
            throw new \Exception('Purchase Order tidak dapat diedit pada status ini.');
        }

        if ($po->quotation_path && Storage::exists($po->quotation_path)) {
            Storage::delete($po->quotation_path);
        }

        $path = $file->store('po/quotations', 'public');
        $po->quotation_path = $path;
        $po->save();

        return $path;
    }

    /**
     * Submit PO for RM approval
     */
    public static function submitForApproval(int $poId): Rst_PurchaseOrder
    {
        return DB::transaction(function () use ($poId) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            if ($po->status !== 'draft' && $po->status !== 'revised') {
                throw new \Exception('Purchase Order harus dalam status draft atau revised.');
            }

            if (! $po->quotation_path) {
                throw new \Exception('Quotation file harus diupload terlebih dahulu.');
            }

            $po->status = 'pending_rm';
            $po->approval_level = 1;
            $po->updated_by = auth()->user()?->username;
            $po->save();

            return $po;
        });
    }

    /**
     * RM approval
     */
    public static function approveByRM(int $poId, ?string $notes = null): Rst_PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $notes) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            if ($po->status !== 'pending_rm') {
                throw new \Exception('Purchase Order tidak dalam status pending_rm.');
            }

            $po->status = 'pending_spv';
            $po->approval_level = 2;
            $po->rm_approved_by = auth()->user()?->id;
            $po->rm_approved_at = now();
            $po->rm_notes = $notes;
            $po->updated_by = auth()->user()?->username;
            $po->save();

            return $po;
        });
    }

    /**
     * SPV approval (final)
     */
    public static function approveBySPV(int $poId, ?string $notes = null): Rst_PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $notes) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            if ($po->status !== 'pending_spv') {
                throw new \Exception('Purchase Order tidak dalam status pending_spv.');
            }

            $po->status = 'approved';
            $po->approval_level = 3;
            $po->spv_approved_by = auth()->user()?->id;
            $po->spv_approved_at = now();
            $po->spv_notes = $notes;
            $po->updated_by = auth()->user()?->username;
            $po->save();

            return $po;
        });
    }

    /**
     * Reject at any approval level - kembali ke draft
     */
    public static function reject(int $poId, string $reason): Rst_PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $reason) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            $po->status = 'rejected';
            $po->rejected_by = auth()->user()?->id;
            $po->rejected_at = now();
            $po->reject_reason = $reason;
            $po->rejected_at_level = $po->approval_level;
            $po->updated_by = auth()->user()?->username;
            $po->save();

            return $po;
        });
    }

    /**
     * Request revision - kembali ke draft (level 1)
     */
    public static function requestRevision(int $poId, string $reason): Rst_PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $reason) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            $currentLevel = $po->approval_level;
            $po->status = 'revised';
            $po->approval_level = 0;
            $po->revise_requested_by = auth()->user()?->id;
            $po->revise_requested_at = now();
            $po->revise_reason = $reason;
            $po->revise_requested_at_level = $currentLevel;
            $po->updated_by = auth()->user()?->username;
            $po->save();

            return $po;
        });
    }

    /**
     * Generate unique PO number
     */
    public static function generatePurchaseOrderNumber(): string
    {
        $prefix = 'PO-'.date('Ymd');
        $lastPO = Rst_PurchaseOrder::where('po_number', 'like', $prefix.'%')
            ->latest('id')
            ->first();

        $seq = $lastPO ? (int) substr($lastPO->po_number, -4) + 1 : 1;

        return $prefix.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get PO list with filters
     */
    public static function getPOList(
        int $locationId,
        ?string $status = null,
        ?string $search = null,
        int $perPage = 15
    ) {
        $query = Rst_PurchaseOrder::where('location_id', $locationId)
            ->with(['purchaseRequest', 'vendor', 'items.item'])
            ->latest('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%$search%")
                    ->orWhere('vendor_name', 'like', "%$search%")
                    ->orWhereHas('purchaseRequest', function ($subQ) use ($search) {
                        $subQ->where('pr_number', 'like', "%$search%");
                    });
            });
        }

        return $query->paginate($perPage);
    }
}
