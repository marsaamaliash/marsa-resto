<?php

namespace App\Models\Holdings\Resto;

use Illuminate\Database\Eloquent\Model;

class Rst_UserBranch extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'user_branches';

    protected $fillable = [
        'auth_user_id',
        'branch_id',
        'is_default',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeForUser($query, int $userId)
    {
        return $query->where('auth_user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
