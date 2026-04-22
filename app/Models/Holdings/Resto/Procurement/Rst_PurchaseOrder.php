<?php

namespace App\Models\Holdings\Resto\Procurement;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_PurchaseOrder extends Model
{
    use BelongsToBranch;
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'purchase_orders';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'branch_id',
        'po_number',
        'purchase_request_id',
        'vendor_id',
        'vendor_name',
        'location_id',
        'payment_by',
        'quotation_path',
        'notes',
        'total_amount',
        'status',
        'received_status',
        'is_closed',
        'payment_status',
        'invoice_number',
        'invoice_date',
        'invoice_path',
        'approval_level',
        'rm_approved_by',
        'rm_approved_at',
        'rm_notes',
        'spv_approved_by',
        'spv_approved_at',
        'spv_notes',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'rejected_at_level',
        'revise_requested_by',
        'revise_requested_at',
        'revise_reason',
        'revise_requested_at_level',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rm_approved_at' => 'datetime',
        'spv_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revise_requested_at' => 'datetime',
        'invoice_date' => 'date',
        'is_closed' => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(Rst_PurchaseRequest::class, 'purchase_request_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterVendor::class, 'vendor_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Rst_PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function goodsReceipts(): HasMany
    {
        return $this->hasMany(Rst_GoodsReceipt::class, 'purchase_order_id');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingRM(): bool
    {
        return $this->status === 'pending_rm';
    }

    public function isPendingSPV(): bool
    {
        return $this->status === 'pending_spv';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isRevised(): bool
    {
        return $this->status === 'revised';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'revised']);
    }

    public function isNotReceived(): bool
    {
        return $this->received_status === 'not_received';
    }

    public function isPartiallyReceived(): bool
    {
        return $this->received_status === 'partial';
    }

    public function isFullyReceived(): bool
    {
        return $this->received_status === 'fully_received';
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === 'unpaid';
    }

    public function isPendingFinance(): bool
    {
        return $this->payment_status === 'pending_finance';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function canReceiveGoods(): bool
    {
        return $this->isApproved() && ! $this->is_closed;
    }

    public function getReceivedStatusBadgeColor(): string
    {
        return match ($this->received_status) {
            'not_received' => 'gray',
            'partial' => 'yellow',
            'fully_received' => 'green',
            default => 'gray',
        };
    }

    public function getPaymentStatusBadgeColor(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'red',
            'pending_finance' => 'yellow',
            'paid' => 'green',
            default => 'gray',
        };
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'pending_rm' => 'yellow',
            'pending_spv' => 'blue',
            'approved' => 'green',
            'rejected' => 'red',
            'revised' => 'orange',
            default => 'gray',
        };
    }
}
