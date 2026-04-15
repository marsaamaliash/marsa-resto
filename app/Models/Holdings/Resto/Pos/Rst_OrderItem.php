<?php

namespace App\Models\Holdings\Resto\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_OrderItem extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'menu_id',
        'quantity',
        'unit_price',
        'subtotal',
        'notes',
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
