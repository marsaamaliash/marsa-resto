<?php

namespace App\Models\Holdings\Resto\Procurement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_PurchaseOrderItem extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'purchase_order_items';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'uom_id',
        'ordered_qty',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'ordered_qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(Rst_PurchaseOrder::class, 'purchase_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }

    public function calculateTotalPrice(): void
    {
        if ($this->unit_price !== null) {
            $this->total_price = $this->ordered_qty * $this->unit_price;
        }
    }
}
