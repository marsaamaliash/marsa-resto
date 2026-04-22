<?php

namespace App\Models\Holdings\Resto\Procurement;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_DirectOrder extends Model
{
    use BelongsToBranch;
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'direct_orders';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'do_number',
        'location_id',
        'purchaser_name',
        'purchase_date',
        'payment_by',
        'proof_path',
        'notes',
        'total_amount',
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
        'revise_requested_by',
        'revise_requested_at',
        'revise_reason',
        'revise_requested_at_level',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'rm_approved_at' => 'datetime',
        'spv_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revise_requested_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Rst_DirectOrderItem::class, 'direct_order_id');
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
