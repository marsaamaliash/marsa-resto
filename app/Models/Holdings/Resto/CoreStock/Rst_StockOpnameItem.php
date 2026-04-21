<?php

namespace App\Models\Holdings\Resto\CoreStock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_StockOpnameItem extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'stock_opname_items';

    protected $guarded = [];

    public function opname(): BelongsTo
    {
        return $this->belongsTo(Rst_StockOpname::class, 'stock_opname_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'location_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }
}
