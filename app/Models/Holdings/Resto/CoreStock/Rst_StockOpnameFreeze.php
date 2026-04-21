<?php

namespace App\Models\Holdings\Resto\CoreStock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_StockOpnameFreeze extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'stock_opname_freezes';

    protected $guarded = [];

    protected $casts = [
        'frozen_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'location_id');
    }
}
