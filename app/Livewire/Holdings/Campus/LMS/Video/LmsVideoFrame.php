<?php

namespace App\Livewire\Holdings\Campus\LMS\Video;

use App\Models\Holdings\Campus\LMS\LmsRoom;
use App\Models\Holdings\Campus\LMS\LmsVideoSession;
use Livewire\Component;

class LmsVideoFrame extends Component
{
    public ?LmsRoom $room = null;

    public string $sessionId;

    public function mount(LmsRoom $room)
    {
        $this->room = $room;
        $this->sessionId = uniqid('sccr-session-');

        LmsVideoSession::create([
            'room_id' => $room->id,
            'session_id' => $this->sessionId,
            'host_nip' => auth()->user()->nip,
            'started_at' => now(),
        ]);
    }

    public function render()
    {
        return view('livewire.holdings.campus.lms.video.lms-video-frame', [
            'room' => $this->room,
            'sessionId' => $this->sessionId,
        ])->layout('components.sccr-layout');
    }
}
