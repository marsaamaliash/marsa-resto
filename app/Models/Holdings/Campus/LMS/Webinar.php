<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class Webinar extends Model
{
    protected $fillable = ['room_id', 'title', 'description', 'start_time', 'end_time', 'status'];

    public function room()
    {
        return $this->belongsTo(LmsRoom::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function recordings()
    {
        return $this->hasMany(Recording::class);
    }
}
