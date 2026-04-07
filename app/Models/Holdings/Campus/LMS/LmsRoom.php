<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class LmsRoom extends Model
{
    protected $table = 'lms_rooms';

    protected $fillable = ['name', 'lecturer_nip', 'kurikulum', 'semester', 'max_participants', 'is_active', 'token'];

    public function webinars()
    {
        return $this->hasMany(Webinar::class);
    }

    public function materials()
    {
        return $this->hasMany(LearningMaterial::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'room_id');
    }
}
