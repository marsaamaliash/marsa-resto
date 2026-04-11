<?php

namespace App\Models\Holdings\Resto\Movement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rst_Movement extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'movements';

    protected $primaryKey = 'id';

    protected $guarded = [
        
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Rst_MovementItem::class, 'movement_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'to_location_id');
    }
}
