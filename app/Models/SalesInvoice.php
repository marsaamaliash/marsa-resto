<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    protected $fillable = [
        'holding_id', 'invoice_number', 'date',
        'customer_name', 'total_amount', 'status',
    ];

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }
}
