<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $fillable = ['holding_id', 'month', 'is_closed'];

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }
}
