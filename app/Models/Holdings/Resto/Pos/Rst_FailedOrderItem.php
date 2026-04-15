<?php

namespace App\Models\Holdings\Resto\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_FailedOrderItem extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'failed_order_items';

    protected $fillable = [
        'original_order_item_id',
        'order_id',
        'menu_id',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
        'reject_reason',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Rst_Order::class, 'order_id');
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Rst_Menu::class, 'menu_id');
    }
}
