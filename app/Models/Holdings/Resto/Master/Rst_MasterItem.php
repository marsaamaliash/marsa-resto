<?php

namespace App\Models\Holdings\Resto\Master;

use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_MasterItem extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'items';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_stockable' => 'boolean',
        'has_batch' => 'boolean',
        'has_expiry' => 'boolean',
        'min_stock' => 'decimal:2',
        'cost_standard' => 'decimal:4',
        'conversion_ratio' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterKategori::class, 'category_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Rst_MasterSatuan::class, 'uom_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function recipeOutputs()
    {
        return $this->hasMany(Rst_Recipe::class, 'output_item_id');
    }
}
