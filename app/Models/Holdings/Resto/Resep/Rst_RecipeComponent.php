<?php

namespace App\Models\Holdings\Resto\Resep;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_RecipeComponent extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'rec_recipe_components';

    protected $fillable = [
        'recipe_version_id',
        'line_no',
        'component_kind',
        'component_item_id',
        'component_recipe_id',
        'stage_code',
        'qty_standard',
        'uom_id',
        'wastage_pct_standard',
        'is_optional',
        'is_modifier_driven',
        'substitution_group_code',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'qty_standard' => 'decimal:6',
        'wastage_pct_standard' => 'decimal:4',
        'is_optional' => 'boolean',
        'is_modifier_driven' => 'boolean',
    ];

    public function recipeVersion()
    {
        return $this->belongsTo(Rst_RecipeVersion::class, 'recipe_version_id');
    }

    public function componentItem()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'component_item_id');
    }

    public function componentRecipe()
    {
        return $this->belongsTo(Rst_Recipe::class, 'component_recipe_id');
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }
}
