<?php

namespace App\Models\Holdings\Resto\CoreStock;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_StockOpnameAdjustment extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'stock_opname_adjustments';

    protected $fillable = [
        'stock_opname_id',
        'item_id',
        'location_id',
        'uom_id',
        'system_qty',
        'physical_qty',
        'difference',
        'status',
        'remark',
    ];

    protected $casts = [
        'system_qty' => 'decimal:2',
        'physical_qty' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(Rst_StockOpname::class, 'stock_opname_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterItem::class, 'item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterLokasi::class, 'location_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterSatuan::class, 'uom_id');
    }
}
