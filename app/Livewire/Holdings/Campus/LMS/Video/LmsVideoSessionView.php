<?php

namespace App\Livewire\Holdings\Campus\LMS\Video;

use App\Models\Holdings\Campus\LMS\LmsRoom;
use App\Models\Holdings\Campus\LMS\LmsVideoSession;
use Livewire\Component;

class LmsVideoSessionView extends Component
{
    public LmsRoom $room;

    public LmsVideoSession $session;

    public function mount(LmsRoom $room)
    {
        $this->room = $room;

        $this->session = LmsVideoSession::create([
            'room_id' => $room->id,
            'session_id' => uniqid('sccr-session-'),
            'host_nip' => auth()->user()->nip,
            'started_at' => now(),
        ]);
    }

    public function render()
    {
        return view('livewire.holdings.campus.lms.video.lms-video-session');
    }
}
