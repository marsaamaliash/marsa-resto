<?php

namespace App\Models\Holdings\Resto\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_Payment extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'subtotal',
        'tax_amount',
        'service_amount',
        'total_amount',
        'payment_method',
        'paid_at',
        'allowance_used',
        'excess_amount',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'allowance_used' => 'decimal:2',
        'excess_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Rst_Order::class, 'order_id');
    }
}
