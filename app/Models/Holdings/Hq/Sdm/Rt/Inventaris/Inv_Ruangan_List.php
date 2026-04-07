<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use Illuminate\Database\Eloquent\Model;

class Inv_Ruangan_List extends Model
{
    protected $table = 'v_inv_ruangan_list';

    protected $primaryKey = 'kode_ruangan'; // Alias PK dari View

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
