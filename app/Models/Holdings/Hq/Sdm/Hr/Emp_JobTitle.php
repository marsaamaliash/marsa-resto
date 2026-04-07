<?php

namespace App\Models\Holdings\Hq\Sdm\Hr;

use Illuminate\Database\Eloquent\Model;

class Emp_JobTitle extends Model
{
    protected $table = 'emp_job_titles';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $casts = [
        'id' => 'integer',
    ];
}
