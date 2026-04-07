<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use Illuminate\Database\Eloquent\Model;

class Inv_Lokasi_List extends Model
{
    protected $table = 'v_inv_lokasi_list';

    protected $primaryKey = 'lokasi_kode';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = []; // read-only
}
