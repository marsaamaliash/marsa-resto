<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;

class AuthNavItem extends Model
{
    protected $table = 'auth_nav_items';

    protected $fillable = [
        'nav_code', 'parent_id', 'module_code', 'label', 'route_name',
        'permission_code', 'icon', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }
}
