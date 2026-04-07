<?php

namespace App\Livewire\Holdings\Campus\LMS\Room;

use App\Models\Holdings\Campus\LMS\LmsRoom;
use App\Traits\ResolvesRoom;
use Livewire\Component;

class LmsRoomManage extends Component
{
    use ResolvesRoom;

    // ✅ Properti publik agar bisa diakses di Blade
    public ?LmsRoom $room = null;

    public $quizzes = [];

    // ✅ mount() menerima model dari route atau parent
    public function mount(?LmsRoom $room = null)
    {
        $this->room = $this->resolveRoom($room);
        $this->quizzes = $this->room?->quizzes()->latest()->get() ?? [];
    }

    public function render()
    {
        if (! $this->room) {
            return view('livewire.error.no-room')->layout('components.sccr-layout');
        }

        return view('livewire.holdings.campus.lms.room.lms-room-manage', [
            'quizzes' => $this->quizzes,
        ])->layout('components.sccr-layout');
    }
}
