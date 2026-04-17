<?php

namespace App\Models\Holdings\Resto\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_PurchaseRequestItem extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'purchase_request_items';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'purchase_request_id',
        'item_id',
        'requested_qty',
        'uom_id',
        'unit_cost',
        'total_cost',
        'is_critical',
        'actual_stock',
        'min_stock',
        'notes',
    ];

    protected $casts = [
        'requested_qty' => 'decimal:6',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'actual_stock' => 'decimal:6',
        'min_stock' => 'decimal:6',
        'is_critical' => 'boolean',
    ];

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(Rst_PurchaseRequest::class, 'purchase_request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }

    public function isCritical(): bool
    {
        return $this->is_critical;
    }

    public function calculateTotalCost(): void
    {
        if ($this->unit_cost !== null) {
            $this->total_cost = $this->requested_qty * $this->unit_cost;
        }
    }
}
