<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = [
        'holding_id', 'name', 'acquisition_date',
        'value', 'depreciation_rate', 'account_id', 'status',
    ];

    public function holding()
    {
        return $this->belongsTo(Holding::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
