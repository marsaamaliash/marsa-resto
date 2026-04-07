<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    protected $fillable = ['quiz_id', 'student_nim', 'total_score', 'duration'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_nim', 'nim');
    }
}
