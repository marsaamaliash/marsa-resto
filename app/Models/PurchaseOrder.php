<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'holding_id', 'po_number', 'date', 'supplier_name',
        'notes', 'status',
    ];

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }
}
