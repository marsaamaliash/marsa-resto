<?php

namespace App\Models\Holdings\Resto\CoreStock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_StockOpname extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'stock_opnames';

    protected $guarded = [];

    protected $casts = [
        'opname_date' => 'date',
        'is_frozen' => 'boolean',
        'exc_chef_approved_at' => 'datetime',
        'rm_approved_at' => 'datetime',
        'spv_approved_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Rst_StockOpnameItem::class, 'stock_opname_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'location_id');
    }
}
