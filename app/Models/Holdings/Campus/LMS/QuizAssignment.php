<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class QuizAssignment extends Model
{
    protected $fillable = ['quiz_id', 'student_nim', 'question_ids'];

    protected $casts = [
        'question_ids' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_nim', 'nim');
    }
}
