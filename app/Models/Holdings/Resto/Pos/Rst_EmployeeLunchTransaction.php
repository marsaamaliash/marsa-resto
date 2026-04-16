<?php

namespace App\Models\Holdings\Resto\Pos;

use Illuminate\Database\Eloquent\Model;

class Rst_EmployeeLunchTransaction extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'employee_lunch_transactions';

    protected $fillable = [
        'employee_number',
        'items',
        'total_amount',
        'allowance_used',
        'excess_amount',
        'payment_method',
        'paid_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'allowance_used' => 'decimal:2',
        'excess_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];
}
