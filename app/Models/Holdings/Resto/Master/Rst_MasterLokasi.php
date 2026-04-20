<?php

namespace App\Models\Holdings\Resto\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_MasterLokasi extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'locations';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'code',
        'type',
        'pic_name',
        'holding_id',
        'branch_id',
        'outlet_id',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
