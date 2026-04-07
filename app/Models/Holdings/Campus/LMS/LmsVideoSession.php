<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsVideoSession extends Model
{
    protected $fillable = [
        'room_id',
        'session_id',
        'host_nip',
        'started_at',
        'ended_at',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(LmsRoom::class);
    }
}
