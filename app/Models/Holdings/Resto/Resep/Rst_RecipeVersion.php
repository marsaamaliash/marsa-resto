<?php

namespace App\Models\Holdings\Resto\Resep;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_RecipeVersion extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'rec_recipe_versions';

    protected $fillable = [
        'recipe_id',
        'version_no',
        'effective_from',
        'effective_to',
        'approval_status',
        'is_active',
        'approval_request_id',
        'batch_size_qty',
        'batch_size_uom_id',
        'expected_output_qty',
        'expected_output_uom_id',
        'expected_yield_pct',
        'standard_loss_pct',
        'notes',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'batch_size_qty' => 'decimal:6',
        'expected_output_qty' => 'decimal:6',
        'expected_yield_pct' => 'decimal:4',
        'standard_loss_pct' => 'decimal:4',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function recipe()
    {
        return $this->belongsTo(Rst_Recipe::class, 'recipe_id');
    }

    public function batchSizeUom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'batch_size_uom_id');
    }

    public function expectedOutputUom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'expected_output_uom_id');
    }

    public function components()
    {
        return $this->hasMany(Rst_RecipeComponent::class, 'recipe_version_id');
    }

    public function outputs()
    {
        return $this->hasMany(Rst_RecipeOutput::class, 'recipe_version_id');
    }

    public function costSnapshots()
    {
        return $this->hasMany(Rst_RecipeCostSnapshot::class, 'recipe_version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDraft($query)
    {
        return $query->where('approval_status', 'draft')->orWhereNull('approval_status');
    }
}
