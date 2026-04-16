<?php

namespace App\Models\Holdings\Resto\Resep;

use Illuminate\Database\Eloquent\Model;

class Rst_RecipeCostSnapshot extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'rec_recipe_cost_snapshots';

    protected $fillable = [
        'recipe_version_id',
        'snapshot_date',
        'cost_basis',
        'material_cost',
        'packaging_cost',
        'overhead_cost',
        'labor_cost',
        'total_batch_cost',
        'cost_per_output_unit',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'material_cost' => 'decimal:4',
        'packaging_cost' => 'decimal:4',
        'overhead_cost' => 'decimal:4',
        'labor_cost' => 'decimal:4',
        'total_batch_cost' => 'decimal:4',
        'cost_per_output_unit' => 'decimal:4',
    ];

    public function recipeVersion()
    {
        return $this->belongsTo(Rst_RecipeVersion::class, 'recipe_version_id');
    }
}
