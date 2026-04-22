<?php

namespace App\Models\Holdings\Resto\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_GoodsReceipt extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'goods_receipts';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'receipt_number',
        'purchase_order_id',
        'location_id',
        'received_by',
        'received_at',
        'status',
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
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rm_approved_at' => 'datetime',
        'spv_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'received_at' => 'datetime',
        'is_closed' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(Rst_PurchaseOrder::class, 'purchase_order_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'location_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Auth\AuthUser::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Rst_GoodsReceiptItem::class, 'goods_receipt_id');
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

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'pending_rm' => 'yellow',
            'pending_spv' => 'blue',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}
