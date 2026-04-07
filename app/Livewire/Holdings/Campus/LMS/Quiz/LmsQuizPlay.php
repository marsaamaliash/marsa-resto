<?php

namespace App\Livewire\Holdings\Campus\LMS\Quiz;

use App\Models\Holdings\Campus\LMS\Quiz;
use App\Models\Holdings\Campus\LMS\QuizAnswer;
use App\Models\Holdings\Campus\LMS\QuizResult;
use App\Models\Holdings\Campus\Student;
use App\Traits\ResolvesQuiz;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LmsQuizPlay extends Component
{
    use ResolvesQuiz;

    public ?Quiz $quiz = null;

    public $questions = [];

    public $answers = [];

    public $submitted = false;

    public $score = 0;

    public function mount(?Quiz $quiz = null)
    {
        $this->quiz = $this->resolveQuiz($quiz);
        $this->questions = $this->quiz?->questions()->get() ?? [];
    }

    public function submitQuiz()
    {
        if (! $this->quiz) {
            return;
        }

        $student = Student::where('nim', auth()->user()->username)->first();
        if (! $student) {
            return;
        }

        DB::transaction(function () use ($student) {
            $totalScore = 0;

            foreach ($this->questions as $question) {
                $answerValue = $this->answers[$question->id] ?? null;

                QuizAnswer::create([
                    'quiz_id' => $this->quiz->id,
                    'student_nim' => $student->nim,
                    'question_id' => $question->id,
                    'answer' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                ]);

                if ($question->type === 'multiple_choice' && $answerValue == $question->correct_option) {
                    $totalScore += 1;
                }
            }

            QuizResult::create([
                'quiz_id' => $this->quiz->id,
                'student_nim' => $student->nim,
                'total_score' => $totalScore,
                'duration' => 0,
            ]);

            $this->score = $totalScore;
            $this->submitted = true;
        });
    }

    public function render()
    {
        if (! $this->quiz) {
            return view('livewire.error.no-quiz')->layout('components.sccr-layout');
        }

        return view('livewire.holdings.campus.lms.quiz.lms-quiz-play')
            ->layout('components.sccr-layout');
    }
}
