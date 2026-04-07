<?php

namespace App\Livewire\Dashboard;

use App\Models\Holdings\Campus\LMS\LmsRoom;
use App\Models\Holdings\Campus\LMS\Webinar;
use Livewire\Component;

class LmsDashboard extends Component
{
    public $rooms;

    public $webinars;

    public function mount()
    {
        $nip = auth()->user()->nip;

        $this->rooms = LmsRoom::where('lecturer_nip', $nip)->get();
        $this->webinars = Webinar::whereIn('room_id', $this->rooms->pluck('id'))->get();
    }

    public function render()
    {
        return view('livewire.dashboard.lms-dashboard')->layout('components.sccr-layout');
    }
}
