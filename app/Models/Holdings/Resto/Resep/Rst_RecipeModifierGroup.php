<?php

namespace App\Models\Holdings\Resto\Resep;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_RecipeModifierGroup extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'rec_recipe_modifier_groups';

    protected $fillable = [
        'recipe_id',
        'group_code',
        'group_name',
        'selection_mode',
        'is_required',
        'min_select',
        'max_select',
        'sort_no',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function recipe()
    {
        return $this->belongsTo(Rst_Recipe::class, 'recipe_id');
    }

    public function components()
    {
        return $this->hasMany(Rst_RecipeModifierComponent::class, 'modifier_group_id');
    }
}
