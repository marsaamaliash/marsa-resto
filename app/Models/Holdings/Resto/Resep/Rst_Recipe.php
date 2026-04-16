<?php

namespace App\Models\Holdings\Resto\Resep;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_Recipe extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'rec_recipes';

    protected $fillable = [
        'holding_id',
        'branch_id',
        'outlet_id',
        'recipe_code',
        'recipe_name',
        'recipe_type',
        'output_item_id',
        'default_uom_id',
        'issue_method',
        'yield_tracking_mode',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function outputItem()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'output_item_id');
    }

    public function defaultUom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'default_uom_id');
    }

    public function versions()
    {
        return $this->hasMany(Rst_RecipeVersion::class, 'recipe_id');
    }

    public function modifierGroups()
    {
        return $this->hasMany(Rst_RecipeModifierGroup::class, 'recipe_id');
    }
}
