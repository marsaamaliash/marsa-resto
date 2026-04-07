<?php

namespace App\Models\Holdings\Campus\LMS;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'type', 'question', 'options', 'correct_index', 'answer_key', 'is_randomizable'];

    protected $casts = [
        'options' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
