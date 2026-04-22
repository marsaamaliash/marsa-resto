<?php

namespace App\Models\Holdings\Resto\Procurement;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rst_GoodsReceiptItem extends Model
{
    use BelongsToBranch;

    protected $connection = 'sccr_resto';

    protected $table = 'goods_receipt_items';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'item_id',
        'ordered_qty',
        'received_qty',
        'damaged_qty',
        'expired_qty',
        'condition_notes',
        'documentation_path',
    ];

    protected $casts = [
        'ordered_qty' => 'decimal:2',
        'received_qty' => 'decimal:2',
        'damaged_qty' => 'decimal:2',
        'expired_qty' => 'decimal:2',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(Rst_GoodsReceipt::class, 'goods_receipt_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(Rst_PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterItem::class, 'item_id');
    }

    public function uom(): BelongsTo
    {
        return $this->purchaseOrderItem->uom();
    }
}
