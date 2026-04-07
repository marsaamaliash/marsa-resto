<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'holding_id', 'code', 'name', 'type', 'unit',
        'purchase_price', 'selling_price', 'is_active',
    ];

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }
}
