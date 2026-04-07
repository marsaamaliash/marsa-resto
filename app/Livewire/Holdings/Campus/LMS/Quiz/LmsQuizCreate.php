<?php

namespace App\Livewire\Holdings\Campus\LMS\Quiz;

use App\Models\Holdings\Campus\LMS\LmsRoom;
use App\Models\Holdings\Campus\LMS\Quiz;
use Livewire\Component;

class LmsQuizCreate extends Component
{
    public $room;

    public $title;

    public $instructions;

    public $start_time;

    public $end_time;

    public function mount(LmsRoom $room)
    {
        $this->room = $room;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ];
    }

    public function save()
    {
        $this->validate();

        Quiz::create([
            'room_id' => $this->room->id,
            'title' => $this->title,
            'instructions' => $this->instructions,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);

        session()->flash('success', 'Kuis berhasil dibuat.');
        $this->reset(['title', 'instructions', 'start_time', 'end_time']);
    }

    public function render()
    {
        return view('livewire.holdings.campus.lms.quiz.lms-quiz-create')->layout('components.sccr-layout');
    }
}
