<?php

namespace App\Models\Holdings\Resto\Resep;

use App\Models\Auth\AuthUser;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_StockRepack extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'stock_repacks';

    protected $primaryKey = 'id';

    protected $guarded = [];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterItem::class, 'item_id');
    }

    public function sourceItem(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterItem::class, 'item_id');
    }

    public function targetItem(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterItem::class, 'item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterLokasi::class, 'location_id');
    }

    public function fromUom(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterSatuan::class, 'from_uoms_id');
    }

    public function toUom(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterSatuan::class, 'to_uoms_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AuthUser::class, 'created_by');
    }
}
