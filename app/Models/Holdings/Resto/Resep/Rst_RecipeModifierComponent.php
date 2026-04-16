<?php

namespace App\Models\Holdings\Resto\Resep;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_RecipeModifierComponent extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'rec_recipe_modifier_components';

    protected $fillable = [
        'modifier_group_id',
        'modifier_code',
        'modifier_name',
        'component_kind',
        'component_item_id',
        'component_recipe_id',
        'additional_qty',
        'uom_id',
        'additional_price',
        'sort_no',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'additional_qty' => 'decimal:6',
        'additional_price' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function modifierGroup()
    {
        return $this->belongsTo(Rst_RecipeModifierGroup::class, 'modifier_group_id');
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
