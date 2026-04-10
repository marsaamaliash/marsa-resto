<?php

namespace App\Models\Holdings\Resto\Master;

use Illuminate\Database\Eloquent\Model;

class Rst_MasterKategori extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'categories';

    protected $primaryKey = 'id';

    protected $guarded = [

    ];
}
