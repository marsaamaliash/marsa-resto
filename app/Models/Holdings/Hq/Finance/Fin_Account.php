<?php

namespace App\Models\Holdings\Hq\Finance;

use Illuminate\Database\Eloquent\Model;

class Fin_Account extends Model
{
    protected $table = 'fin_accounts';

    protected $primaryKey = 'id';

    protected $fillable = [
        'holding_id', 'department_id', 'division_id',
        'code', 'name', 'type',
        'subcategory_id', 'parent_id',
        'is_active', 'status', 'requested_at',
    ];

    protected $casts = [
        'holding_id' => 'integer',
        'department_id' => 'integer',
        'division_id' => 'integer',
        'subcategory_id' => 'integer',
        'parent_id' => 'integer',
        'is_active' => 'boolean',
        'requested_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
