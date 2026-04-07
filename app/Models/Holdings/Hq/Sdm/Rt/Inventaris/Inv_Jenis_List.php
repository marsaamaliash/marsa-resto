<?php

namespace App\Models\Holdings\Hq\Sdm\Rt\Inventaris;

use Illuminate\Database\Eloquent\Model;

class Inv_Jenis_List extends Model
{
    protected $table = 'v_inv_jenis_list';

    protected $primaryKey = 'jenis_kode'; // Alias PK dari View

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
