<?php

namespace App\Models\Holdings\Resto\CoreStock;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
use Illuminate\Database\Eloquent\Model;

class Rst_RequestActivity extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'request_activities';

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

    public function movement()
    {
        return $this->belongsTo(Rst_Movement::class);
    }
}
