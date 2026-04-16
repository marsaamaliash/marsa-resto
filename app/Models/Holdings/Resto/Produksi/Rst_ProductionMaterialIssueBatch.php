<?php

namespace App\Models\Holdings\Resto\Produksi;

use App\Models\Holdings\Resto\CoreStock\Rst_InventoryBatch;
use Illuminate\Database\Eloquent\Model;

class Rst_ProductionMaterialIssueBatch extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'prod_material_issue_batches';

    protected $fillable = [
        'issue_line_id',
        'inventory_batch_id',
        'batch_no',
        'expiry_date',
        'qty_issued_base',
        'unit_cost',
        'total_cost',
        'issue_sequence',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'qty_issued_base' => 'decimal:6',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function issueLine()
    {
        return $this->belongsTo(Rst_ProductionMaterialIssueLine::class, 'issue_line_id');
    }

    public function inventoryBatch()
    {
        return $this->belongsTo(Rst_InventoryBatch::class, 'inventory_batch_id');
    }
}
