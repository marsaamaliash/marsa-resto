<?php

namespace App\Models\Holdings\Resto\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_Meja extends Model
{
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'meja';

    protected $fillable = [
        'table_number',
        'capacity',
        'area',
        'status',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];
}
