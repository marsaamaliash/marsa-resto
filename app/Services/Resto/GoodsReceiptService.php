<?php

namespace App\Services\Resto;

use App\Models\Holdings\Resto\Procurement\Rst_GoodsReceipt;
use App\Models\Holdings\Resto\Procurement\Rst_GoodsReceiptItem;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GoodsReceiptService
{
    public static function createReceipt(int $poId): Rst_GoodsReceipt
    {
        return DB::transaction(function () use ($poId) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            if (! $po->isApproved()) {
                throw new \Exception('Purchase Order harus dalam status approved.');
            }

            if ($po->is_closed) {
                throw new \Exception('Purchase Order sudah closed, tidak bisa membuat receipt baru.');
            }

            $receipt = Rst_GoodsReceipt::create([
                'receipt_number' => self::generateReceiptNumber(),
                'purchase_order_id' => $poId,
                'location_id' => $po->location_id,
                'status' => 'draft',
                'approval_level' => 0,
                'created_by' => auth()->user()?->id,
            ]);

            foreach ($po->items as $poItem) {
                $alreadyReceived = self::getAlreadyReceivedQty($poItem->id);
                $remainingQty = max(0, $poItem->ordered_qty - $alreadyReceived);

                if ($remainingQty > 0) {
                    Rst_GoodsReceiptItem::create([
                        'goods_receipt_id' => $receipt->id,
                        'purchase_order_item_id' => $poItem->id,
                        'item_id' => $poItem->item_id,
                        'ordered_qty' => $remainingQty,
                        'received_qty' => 0,
                        'damaged_qty' => 0,
                        'expired_qty' => 0,
                    ]);
                }
            }

            return $receipt;
        });
    }

    public static function receiveItems(int $receiptId, array $itemsData, ?string $notes = null, $documentationFiles = null): Rst_GoodsReceipt
    {
        return DB::transaction(function () use ($receiptId, $itemsData, $notes, $documentationFiles) {
            $receipt = Rst_GoodsReceipt::findOrFail($receiptId);

            if (! $receipt->canBeEdited()) {
                throw new \Exception('Goods Receipt tidak dapat diedit pada status ini.');
            }

            $receipt->notes = $notes;
            $receipt->received_by = auth()->user()?->id;
            $receipt->received_at = now();
            $receipt->updated_by = auth()->user()?->id;
            $receipt->save();

            foreach ($itemsData as $index => $itemData) {
                $receiptItem = Rst_GoodsReceiptItem::where('goods_receipt_id', $receiptId)
                    ->where('id', $itemData['id'])
                    ->firstOrFail();

                $receivedQty = $itemData['received_qty'] ?? 0;
                $damagedQty = $itemData['damaged_qty'] ?? 0;
                $expiredQty = $itemData['expired_qty'] ?? 0;
                $conditionNotes = $itemData['condition_notes'] ?? null;

                if ($receivedQty + $damagedQty + $expiredQty > $receiptItem->ordered_qty) {
                    throw new \Exception('Total qty (received + damaged + expired) tidak boleh melebihi ordered qty.');
                }

                $docPath = null;
                if (isset($documentationFiles[$index]) && $documentationFiles[$index]) {
                    $docPath = $documentationFiles[$index]->store('goods-receipt/documentation', 'public');
                }

                $receiptItem->received_qty = $receivedQty;
                $receiptItem->damaged_qty = $damagedQty;
                $receiptItem->expired_qty = $expiredQty;
                $receiptItem->condition_notes = $conditionNotes;
                if ($docPath) {
                    $receiptItem->documentation_path = $docPath;
                }
                $receiptItem->save();
            }

            return $receipt;
        });
    }

    public static function submitForApproval(int $receiptId): Rst_GoodsReceipt
    {
        return DB::transaction(function () use ($receiptId) {
            $receipt = Rst_GoodsReceipt::findOrFail($receiptId);

            if (! $receipt->isDraft()) {
                throw new \Exception('Goods Receipt harus dalam status draft.');
            }

            $hasItems = $receipt->items()->where('received_qty', '>', 0)->exists();
            if (! $hasItems) {
                throw new \Exception('Minimal satu item harus diterima.');
            }

            $receipt->status = 'pending_rm';
            $receipt->approval_level = 1;
            $receipt->updated_by = auth()->user()?->id;
            $receipt->save();

            return $receipt;
        });
    }

    public static function approveByRM(int $receiptId, ?string $notes = null): Rst_GoodsReceipt
    {
        return DB::transaction(function () use ($receiptId, $notes) {
            $receipt = Rst_GoodsReceipt::findOrFail($receiptId);

            if ($receipt->status !== 'pending_rm') {
                throw new \Exception('Goods Receipt tidak dalam status pending_rm.');
            }

            $receipt->status = 'pending_spv';
            $receipt->approval_level = 2;
            $receipt->rm_approved_by = auth()->user()?->id;
            $receipt->rm_approved_at = now();
            $receipt->rm_notes = $notes;
            $receipt->updated_by = auth()->user()?->id;
            $receipt->save();

            return $receipt;
        });
    }

    public static function approveBySPV(int $receiptId, ?string $notes = null): Rst_GoodsReceipt
    {
        return DB::transaction(function () use ($receiptId, $notes) {
            $receipt = Rst_GoodsReceipt::findOrFail($receiptId);

            if ($receipt->status !== 'pending_spv') {
                throw new \Exception('Goods Receipt tidak dalam status pending_spv.');
            }

            $receipt->status = 'approved';
            $receipt->approval_level = 3;
            $receipt->spv_approved_by = auth()->user()?->id;
            $receipt->spv_approved_at = now();
            $receipt->spv_notes = $notes;
            $receipt->updated_by = auth()->user()?->id;
            $receipt->save();

            self::updateStock($receipt);
            self::updatePOReceivedStatus($receipt->purchase_order_id);

            return $receipt;
        });
    }

    public static function reject(int $receiptId, string $reason): Rst_GoodsReceipt
    {
        return DB::transaction(function () use ($receiptId, $reason) {
            $receipt = Rst_GoodsReceipt::findOrFail($receiptId);

            if (! in_array($receipt->status, ['pending_rm', 'pending_spv'])) {
                throw new \Exception('Goods Receipt tidak dalam status pending approval.');
            }

            $receipt->status = 'rejected';
            $receipt->rejected_by = auth()->user()?->id;
            $receipt->rejected_at = now();
            $receipt->reject_reason = $reason;
            $receipt->rejected_at_level = $receipt->approval_level;
            $receipt->updated_by = auth()->user()?->id;
            $receipt->save();

            return $receipt;
        });
    }

    public static function updateInvoice(int $poId, ?string $invoiceNumber = null, ?string $invoiceDate = null, $invoiceFile = null): Rst_PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $invoiceNumber, $invoiceDate, $invoiceFile) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            if (! $po->isFullyReceived()) {
                throw new \Exception('Purchase Order harus fully received sebelum upload invoice.');
            }

            if ($invoiceFile) {
                if ($po->invoice_path && Storage::exists($po->invoice_path)) {
                    Storage::delete($po->invoice_path);
                }
                $po->invoice_path = $invoiceFile->store('po/invoices', 'public');
            }

            if ($invoiceNumber) {
                $po->invoice_number = $invoiceNumber;
            }

            if ($invoiceDate) {
                $po->invoice_date = $invoiceDate;
            }

            if ($po->isUnpaid()) {
                $po->payment_status = 'pending_finance';
            }

            $po->updated_by = auth()->user()?->id;
            $po->save();

            return $po;
        });
    }

    public static function markAsPaid(int $poId): Rst_PurchaseOrder
    {
        return DB::transaction(function () use ($poId) {
            $po = Rst_PurchaseOrder::findOrFail($poId);

            if (! $po->isPendingFinance()) {
                throw new \Exception('Purchase Order tidak dalam status pending finance.');
            }

            $po->payment_status = 'paid';
            $po->updated_by = auth()->user()?->id;
            $po->save();

            return $po;
        });
    }

    private static function updateStock(Rst_GoodsReceipt $receipt): void
    {
        $userId = Auth::id();

        foreach ($receipt->items as $item) {
            if ($item->received_qty > 0) {
                $uomId = $item->purchaseOrderItem->uom_id ?? $item->item()->first()?->default_uom_id;

                StockMutationService::addMutation(
                    $item->item_id,
                    $receipt->location_id,
                    $uomId,
                    $item->received_qty,
                    'in',
                    'Goods Receipt: '.$receipt->receipt_number.' (PO: '.$receipt->purchaseOrder->po_number.')',
                    null,
                    null,
                    $userId
                );
            }

            if ($item->damaged_qty > 0 || $item->expired_qty > 0) {
                $wasteQty = $item->damaged_qty + $item->expired_qty;
                $uomId = $item->purchaseOrderItem->uom_id ?? $item->item()->first()?->default_uom_id;

                StockMutationService::addMutation(
                    $item->item_id,
                    $receipt->location_id,
                    $uomId,
                    $wasteQty,
                    'waste',
                    'Goods Receipt waste: '.$receipt->receipt_number.' (PO: '.$receipt->purchaseOrder->po_number.')',
                    null,
                    null,
                    $userId
                );
            }
        }
    }

    private static function updatePOReceivedStatus(int $poId): void
    {
        $po = Rst_PurchaseOrder::findOrFail($poId);

        $totalOrdered = $po->items()->sum('ordered_qty');
        $totalReceived = 0;

        foreach ($po->items as $poItem) {
            $totalReceived += self::getAlreadyReceivedQty($poItem->id);
        }

        if ($totalReceived >= $totalOrdered && $totalOrdered > 0) {
            $po->received_status = 'fully_received';
            $po->is_closed = true;
        } else {
            $po->received_status = 'partial';
        }

        $po->updated_by = auth()->user()?->id;
        $po->save();
    }

    private static function getAlreadyReceivedQty(int $poItemId): float
    {
        return Rst_GoodsReceiptItem::where('purchase_order_item_id', $poItemId)
            ->whereHas('goodsReceipt', function ($q) {
                $q->where('status', 'approved');
            })
            ->sum('received_qty');
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'GR-'.date('Ymd');
        $lastGR = Rst_GoodsReceipt::where('receipt_number', 'like', $prefix.'%')
            ->latest('id')
            ->first();

        $seq = $lastGR ? (int) substr($lastGR->receipt_number, -4) + 1 : 1;

        return $prefix.'-'.str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public static function getReceiptList(?string $status = null, ?string $search = null, ?int $locationId = null, int $perPage = 15)
    {
        $query = Rst_GoodsReceipt::with(['purchaseOrder', 'location', 'receivedBy', 'items.item', 'items.purchaseOrderItem'])
            ->latest('created_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', function ($subQ) use ($search) {
                        $subQ->where('po_number', 'like', "%{$search}%")
                            ->orWhere('vendor_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('location', function ($subQ) use ($search) {
                        $subQ->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage);
    }

    public static function getInvoiceList(?string $paymentStatus = null, ?string $paymentBy = null, ?string $search = null, int $perPage = 15)
    {
        $query = Rst_PurchaseOrder::with(['vendor', 'location', 'goodsReceipts'])
            ->whereNotNull('invoice_number')
            ->orWhereNotNull('invoice_path')
            ->latest('updated_at');

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($paymentBy) {
            $query->where('payment_by', $paymentBy);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }
}
