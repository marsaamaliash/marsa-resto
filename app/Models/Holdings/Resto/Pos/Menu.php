<?php

namespace App\Models\Holdings\Resto\Pos;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $connection = 'sccr_resto';

    protected $table = 'menus';

    protected $fillable = [
        'recipe_id',
        'name',
        'price',
        'category',
        'customer_segment',
        'is_active',
        'description',
        'image',
        'stock',
        'discount',
        'slug',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
    ];
}
