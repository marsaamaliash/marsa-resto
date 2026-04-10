<?php

namespace App\Models\Holdings\Resto\Movement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_MovementItem extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'movement_items';

    protected $primaryKey = 'id';

    protected $fillable = [
        'movement_id',
        'item_id',
        'qty',
        'uom_id',
        'remark',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Rst_Movement::class, 'movement_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }
}
