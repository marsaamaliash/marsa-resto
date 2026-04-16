<?php

namespace App\Models\Holdings\Resto\Produksi;

use Illuminate\Database\Eloquent\Model;

class Rst_ProductionCostSummary extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'prod_cost_summaries';

    protected $fillable = [
        'prod_order_id',
        'cost_center_id',
        'material_cost_total',
        'packaging_cost_total',
        'labor_absorbed_total',
        'overhead_absorbed_total',
        'normal_loss_cost_total',
        'abnormal_waste_cost_total',
        'total_input_cost',
        'total_output_cost',
        'yield_variance_cost',
        'cost_per_output_unit',
        'computed_at',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'material_cost_total' => 'decimal:4',
        'packaging_cost_total' => 'decimal:4',
        'labor_absorbed_total' => 'decimal:4',
        'overhead_absorbed_total' => 'decimal:4',
        'normal_loss_cost_total' => 'decimal:4',
        'abnormal_waste_cost_total' => 'decimal:4',
        'total_input_cost' => 'decimal:4',
        'total_output_cost' => 'decimal:4',
        'yield_variance_cost' => 'decimal:4',
        'cost_per_output_unit' => 'decimal:4',
        'computed_at' => 'datetime',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(Rst_ProductionOrder::class, 'prod_order_id');
    }
}
