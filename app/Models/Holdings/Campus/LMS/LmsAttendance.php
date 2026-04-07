<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsAttendance extends Model
{
    protected $table = 'lms_attendances';

    protected $fillable = ['participant_id', 'duration', 'ip_address', 'device'];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
