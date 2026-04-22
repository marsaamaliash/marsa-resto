<?php

namespace App\Models\Holdings\Resto\Procurement;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_DirectOrderItem extends Model
{
    use BelongsToBranch;

    protected $connection = 'sccr_resto';

    protected $table = 'direct_order_items';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'direct_order_id',
        'item_id',
        'uom_id',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function directOrder(): BelongsTo
    {
        return $this->belongsTo(Rst_DirectOrder::class, 'direct_order_id');
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
        $this->total_price = $this->quantity * $this->unit_price;
    }
}
