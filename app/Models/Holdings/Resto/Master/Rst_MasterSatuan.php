<?php

namespace App\Models\Holdings\Resto\Master;

use Illuminate\Database\Eloquent\Model;

class Rst_MasterSatuan extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'uoms';

    protected $primaryKey = 'id';

    protected $guarded = [

    ];
}
