<?php

namespace App\Models\Holdings\Resto\Produksi;

use Illuminate\Database\Eloquent\Model;

class Rst_ProductionMaterialIssueLine extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'prod_material_issue_lines';

    protected $fillable = [
        'prod_order_id',
        'line_no',
        'plan_line_id',
        'issue_type',
        'item_id',
        'issue_location_id',
        'qty_issued',
        'uom_id',
        'base_qty_issued',
        'actual_unit_cost',
        'actual_total_cost',
        'costing_method_used',
        'reason_code',
        'notes',
        'issued_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'qty_issued' => 'decimal:6',
        'base_qty_issued' => 'decimal:6',
        'actual_unit_cost' => 'decimal:4',
        'actual_total_cost' => 'decimal:4',
        'issued_at' => 'datetime',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(Rst_ProductionOrder::class, 'prod_order_id');
    }

    public function planLine()
    {
        return $this->belongsTo(Rst_ProductionOrderComponentPlan::class, 'plan_line_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'item_id');
    }

    public function issueLocation()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'issue_location_id');
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }

    public function batches()
    {
        return $this->hasMany(Rst_ProductionMaterialIssueBatch::class, 'issue_line_id');
    }
}
