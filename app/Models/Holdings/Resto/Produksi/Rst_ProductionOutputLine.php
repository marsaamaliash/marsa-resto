<?php

namespace App\Models\Holdings\Resto\Produksi;

use App\Models\Holdings\Resto\CoreStock\Rst_InventoryBatch;
use Illuminate\Database\Eloquent\Model;

class Rst_ProductionOutputLine extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'prod_output_lines';

    protected $fillable = [
        'prod_order_id',
        'line_no',
        'output_type',
        'output_item_id',
        'output_location_id',
        'qty_output',
        'uom_id',
        'inventory_batch_id',
        'actual_total_cost_allocated',
        'actual_unit_cost',
        'qc_status',
        'posted_to_inventory',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'qty_output' => 'decimal:6',
        'actual_total_cost_allocated' => 'decimal:4',
        'actual_unit_cost' => 'decimal:4',
        'posted_to_inventory' => 'boolean',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(Rst_ProductionOrder::class, 'prod_order_id');
    }

    public function outputItem()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'output_item_id');
    }

    public function outputLocation()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'output_location_id');
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(Rst_InventoryBatch::class, 'inventory_batch_id');
    }
}
