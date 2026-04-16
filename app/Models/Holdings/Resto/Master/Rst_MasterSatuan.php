<?php

namespace App\Models\Holdings\Resto\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_MasterSatuan extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'uoms';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'symbols',
        'abbreviation',
        'is_active',
        'holding_id',
        'branch_id',
        'outlet_id',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
