<?php

namespace App\Models\Holdings\Resto\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rst_Order extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'customer_name',
        'table_number',
        'total_amount',
        'notes',
        'created_by',
        'payment_status',
        'paid_at',
        'employee_number',
        'order_type',
        'allowance_used',
        'excess_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_by' => 'integer',
        'paid_at' => 'datetime',
        'allowance_used' => 'decimal:2',
        'excess_amount' => 'decimal:2',
    ];

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', '!=', 'paid');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Rst_OrderItem::class, 'order_id');
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();

        $sequence = $lastOrder
            ? (int) substr($lastOrder->order_number, -4) + 1
            : 1;

        return $prefix.$date.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
