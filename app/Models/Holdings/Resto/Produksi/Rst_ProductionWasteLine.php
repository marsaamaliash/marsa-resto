<?php

namespace App\Models\Holdings\Resto\Produksi;

use App\Models\Holdings\Resto\CoreStock\Rst_InventoryBatch;
use Illuminate\Database\Eloquent\Model;

class Rst_ProductionWasteLine extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'prod_waste_lines';

    protected $fillable = [
        'prod_order_id',
        'line_no',
        'waste_stage',
        'waste_type',
        'item_id',
        'inventory_batch_id',
        'qty_waste',
        'uom_id',
        'base_qty_waste',
        'actual_unit_cost',
        'actual_total_cost',
        'charge_mode',
        'reason_code',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'qty_waste' => 'decimal:6',
        'base_qty_waste' => 'decimal:6',
        'actual_unit_cost' => 'decimal:4',
        'actual_total_cost' => 'decimal:4',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(Rst_ProductionOrder::class, 'prod_order_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'item_id');
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(Rst_InventoryBatch::class, 'inventory_batch_id');
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }
}
