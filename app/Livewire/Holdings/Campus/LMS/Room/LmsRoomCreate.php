<?php

namespace App\Livewire\Holdings\Campus\LMS\Room;

use App\Models\Holdings\Campus\LMS\LmsRoom;
use Illuminate\Support\Str;
use Livewire\Component;

class LmsRoomCreate extends Component
{
    public $name;

    public $kurikulum;

    public $semester;

    public $max_participants = 10000;

    public function rules()
    {
        return [
            'name' => 'required|string|unique:lms_rooms,name',
            'kurikulum' => 'required|string',
            'semester' => 'required|string',
            'max_participants' => 'required|integer|min:1|max:10000',
        ];
    }

    public function save()
    {
        $this->validate();

        LmsRoom::create([
            'name' => $this->name,
            'lecturer_nip' => auth()->user()->nip, // diasumsikan login sebagai dosen
            'kurikulum' => $this->kurikulum,
            'semester' => $this->semester,
            'max_participants' => $this->max_participants,
            'is_active' => true,
            'token' => Str::random(32),
        ]);

        session()->flash('success', 'Room LMS berhasil dibuat.');
        $this->reset(['name', 'kurikulum', 'semester', 'max_participants']);
    }

    public function render()
    {
        return view('livewire.holdings.campus.lms.room.lms-room-create')->layout('components.sccr-layout');
    }
}
