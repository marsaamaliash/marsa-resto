<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use Illuminate\Database\Eloquent\Model;

class Inv_Holding_List extends Model
{
    protected $table = 'v_inv_holding_list';

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'kode';

    protected $keyType = 'string';
}
