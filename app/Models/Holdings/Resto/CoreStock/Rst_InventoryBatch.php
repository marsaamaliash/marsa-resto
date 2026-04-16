<?php

namespace App\Models\Holdings\Resto\CoreStock;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_InventoryBatch extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'inv_inventory_batches';

    protected $fillable = [
        'holding_id',
        'branch_id',
        'outlet_id',
        'item_id',
        'location_id',
        'batch_no',
        'expiry_date',
        'qty_received',
        'qty_remaining',
        'uom_id',
        'unit_cost',
        'total_cost',
        'costing_method',
        'received_at',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'qty_received' => 'decimal:6',
        'qty_remaining' => 'decimal:6',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'received_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'item_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'location_id');
    }

    public function uom()
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterSatuan::class, 'uom_id');
    }
}
