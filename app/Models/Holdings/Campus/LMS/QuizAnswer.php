<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    protected $fillable = ['quiz_id', 'student_nim', 'question_id', 'answer', 'score'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_nim', 'nim');
    }
}
