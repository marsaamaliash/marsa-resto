<?php

namespace App\Models\Holdings\Resto\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rst_PurchaseRequest extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'purchase_requests';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'pr_number',
        'requester_location_id',
        'vendor_name',
        'status',
        'approval_level',
        'notes',
        'required_date',
        'total_estimated_cost',
        'requested_by',
        'requested_at',
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
        'requested_at' => 'datetime',
        'rm_approved_at' => 'datetime',
        'spv_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revise_requested_at' => 'datetime',
        'required_date' => 'date',
        'total_estimated_cost' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Rst_PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function requesterLocation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'requester_location_id');
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
