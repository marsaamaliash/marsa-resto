<?php

namespace App\Models\Holdings\Resto\Produksi;

use Illuminate\Database\Eloquent\Model;

class Rst_ProductionOrderComponentPlan extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'prod_order_component_plans';

    protected $fillable = [
        'prod_order_id',
        'line_no',
        'component_kind',
        'component_item_id',
        'component_recipe_id',
        'stage_code',
        'qty_standard_per_batch',
        'planned_total_qty',
        'uom_id',
        'standard_unit_cost',
        'standard_total_cost',
        'wastage_pct_standard',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'qty_standard_per_batch' => 'decimal:6',
        'planned_total_qty' => 'decimal:6',
        'standard_unit_cost' => 'decimal:4',
        'standard_total_cost' => 'decimal:4',
        'wastage_pct_standard' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(Rst_ProductionOrder::class, 'prod_order_id');
    }

    public function componentItem()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'component_item_id');
    }

    public function componentRecipe()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Resep\Rst_Recipe::class, 'component_recipe_id');
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }
}
