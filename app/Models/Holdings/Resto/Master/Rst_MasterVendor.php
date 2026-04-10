<?php

namespace App\Models\Holdings\Resto\Master;

use Illuminate\Database\Eloquent\Model;

class Rst_MasterVendor extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'vendors';

    protected $primaryKey = 'id';

    protected $guarded = [

    ];
}
