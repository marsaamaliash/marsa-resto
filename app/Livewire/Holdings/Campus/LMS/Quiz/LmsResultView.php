<?php

namespace App\Livewire\Holdings\Campus\LMS\Quiz;

use App\Models\Holdings\Campus\LMS\Quiz;
use App\Traits\ResolvesQuiz;
use Livewire\Component;

class LmsResultView extends Component
{
    use ResolvesQuiz;

    public ?Quiz $quiz = null;

    public $results = [];

    public function mount(?Quiz $quiz = null)
    {
        $this->quiz = $this->resolveQuiz($quiz);
        $this->results = $this->quiz?->results()->with('student')->get() ?? [];
    }

    public function render()
    {
        if (! $this->quiz) {
            return view('livewire.error.no-quiz')->layout('components.sccr-layout');
        }

        return view('livewire.holdings.campus.lms.quiz.lms-result-view')
            ->layout('components.sccr-layout');
    }
}
