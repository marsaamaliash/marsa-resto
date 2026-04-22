<?php

namespace App\Models\Holdings\Resto\Master;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_MasterKategori extends Model
{
    use BelongsToBranch;
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'categories';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
