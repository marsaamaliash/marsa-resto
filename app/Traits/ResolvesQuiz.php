<?php

namespace App\Traits;

use App\Models\Holdings\Campus\LMS\Quiz;

trait ResolvesQuiz
{
    public function resolveQuiz(?Quiz $quiz = null): ?Quiz
    {
        if ($quiz) {
            return $quiz;
        }

        $nip = auth()->user()->nip ?? null;

        return $nip
            ? Quiz::whereHas('room', fn ($q) => $q->where('lecturer_nip', $nip))->latest()->first()
            : null;
    }
}
