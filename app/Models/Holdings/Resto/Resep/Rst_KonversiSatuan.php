<?php

namespace App\Models\Holdings\Resto\Resep;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_KonversiSatuan extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'uom_conversions';

    protected $primaryKey = 'id';

    protected $fillable = [
        'item_id',
        'from_uoms_id',
        'to_uoms_id',
        'conversion_factor',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterItem::class, 'item_id');
    }

    public function fromUom(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterSatuan::class, 'from_uoms_id');
    }

    public function toUom(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterSatuan::class, 'to_uoms_id');
    }
}
