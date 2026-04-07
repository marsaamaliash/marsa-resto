<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['room_id', 'title', 'instructions', 'start_time', 'end_time'];

    public function room()
    {
        return $this->belongsTo(LmsRoom::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function assignments()
    {
        return $this->hasMany(QuizAssignment::class);
    }

    public function results()
    {
        return $this->hasMany(QuizResult::class);
    }
}
