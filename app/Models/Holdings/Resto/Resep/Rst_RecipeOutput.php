<?php

namespace App\Models\Holdings\Resto\Resep;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_RecipeOutput extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'rec_recipe_outputs';

    protected $fillable = [
        'recipe_version_id',
        'line_no',
        'output_type',
        'output_item_id',
        'planned_qty',
        'uom_id',
        'cost_allocation_pct',
        'is_inventory_item',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'planned_qty' => 'decimal:6',
        'cost_allocation_pct' => 'decimal:4',
        'is_inventory_item' => 'boolean',
    ];

    public function recipeVersion()
    {
        return $this->belongsTo(Rst_RecipeVersion::class, 'recipe_version_id');
    }

    public function outputItem()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'output_item_id');
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }
}
