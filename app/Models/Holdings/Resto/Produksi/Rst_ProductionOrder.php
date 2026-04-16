<?php

namespace App\Models\Holdings\Resto\Produksi;

use App\Models\Holdings\Resto\Resep\Rst_Recipe;
use App\Models\Holdings\Resto\Resep\Rst_RecipeVersion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_ProductionOrder extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'prod_orders';

    protected $fillable = [
        'holding_id',
        'branch_id',
        'outlet_id',
        'cost_center_id',
        'prod_no',
        'prod_type',
        'recipe_id',
        'recipe_version_id',
        'source_table',
        'source_id',
        'source_no',
        'issue_location_id',
        'output_location_id',
        'planned_output_qty',
        'actual_output_qty',
        'output_uom_id',
        'status',
        'approval_status',
        'approval_request_id',
        'business_date',
        'started_at',
        'completed_at',
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
        'planned_output_qty' => 'decimal:6',
        'actual_output_qty' => 'decimal:6',
        'business_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function recipe()
    {
        return $this->belongsTo(Rst_Recipe::class, 'recipe_id');
    }

    public function recipeVersion()
    {
        return $this->belongsTo(Rst_RecipeVersion::class, 'recipe_version_id');
    }

    public function issueLocation()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'issue_location_id');
    }

    public function outputLocation()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'output_location_id');
    }

    public function outputUom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'output_uom_id');
    }

    public function componentPlans()
    {
        return $this->hasMany(Rst_ProductionOrderComponentPlan::class, 'prod_order_id');
    }

    public function materialIssueLines()
    {
        return $this->hasMany(Rst_ProductionMaterialIssueLine::class, 'prod_order_id');
    }

    public function outputLines()
    {
        return $this->hasMany(Rst_ProductionOutputLine::class, 'prod_order_id');
    }

    public function wasteLines()
    {
        return $this->hasMany(Rst_ProductionWasteLine::class, 'prod_order_id');
    }

    public function costSummary()
    {
        return $this->hasOne(Rst_ProductionCostSummary::class, 'prod_order_id');
    }
}
