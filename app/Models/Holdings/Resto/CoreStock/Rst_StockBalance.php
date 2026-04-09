<?php

namespace App\Models\Holdings\Resto\CoreStock;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Illuminate\Database\Eloquent\Model;

class Rst_StockBalance extends Model
{
    protected $connection = 'sccr_resto';
    
    protected $table = 'stock_balances';

    protected $primaryKey = 'id';

    protected $guarded = [
        
    ];

    public function item()
{
    return $this->belongsTo(Rst_MasterItem::class);
}

public function location()
{
    return $this->belongsTo(Rst_MasterLokasi::class);
}

public function uom()
{
    return $this->belongsTo(Rst_MasterSatuan::class);
}
}
