<?php

namespace App\Models\Holdings\Hq\Sdm\Hr;

use Illuminate\Database\Eloquent\Model;

class Emp_Position extends Model
{
    protected $table = 'emp_positions';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $casts = [
        'id' => 'integer',
        'level' => 'integer',
    ];
}
