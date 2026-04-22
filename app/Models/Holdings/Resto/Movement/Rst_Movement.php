<?php

namespace App\Models\Holdings\Resto\Movement;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rst_Movement extends Model
{
    use BelongsToBranch;
    use SoftDeletes;

    protected $connection = 'sccr_resto';

    protected $table = 'movements';

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $fillable = [
        'branch_id',
        'reference_number',
        'request_number',
        'request_date',
        'from_location_id',
        'to_location_id',
        'pic_name',
        'approved_by_name',
        'type',
        'status',
        'remark',
        'approval_level',
        'exc_chef_approved_by',
        'exc_chef_approved_at',
        'rm_approved_by',
        'rm_approved_at',
        'spv_approved_by',
        'spv_approved_at',
    ];

    protected $casts = [
        'request_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(Rst_MovementItem::class, 'movement_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Holdings\Resto\Master\Rst_MasterLokasi::class, 'to_location_id');
    }
}
