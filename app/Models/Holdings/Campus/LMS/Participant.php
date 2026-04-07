<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = ['student_nim', 'webinar_id', 'role', 'joined_at', 'left_at'];

    public function webinar()
    {
        return $this->belongsTo(Webinar::class);
    }

    public function attendance()
    {
        return $this->hasOne(LmsAttendance::class);
    }
}
