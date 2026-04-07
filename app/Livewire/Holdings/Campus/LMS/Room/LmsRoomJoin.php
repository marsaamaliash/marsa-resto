<?php

namespace App\Livewire\Holdings\Campus\LMS\Room;

use App\Models\Holdings\Campus\LMS\LmsRoom;
use App\Models\Holdings\Campus\LMS\LmsRoomParticipant;
use App\Models\Holdings\Campus\Student;
use App\Traits\ResolvesRoom;
use Livewire\Component;

class LmsRoomJoin extends Component
{
    use ResolvesRoom;

    public ?LmsRoom $room = null;

    public bool $joined = false;

    public function mount(?LmsRoom $room = null)
    {

        $this->room = $this->resolveRoom($room);

        if (! $this->room) {
            return;
        }

        $student = Student::where('nim', auth()->user()->username)->first();

        if ($student) {
            $this->joined = LmsRoomParticipant::where('room_id', $this->room->id)
                ->where('student_nim', $student->nim)
                ->exists();
        }
    }

    public function joinRoom()
    {
        $student = Student::where('nim', auth()->user()->username)->first();

        if (! $student) {
            session()->flash('error', 'Mahasiswa tidak ditemukan.');

            return;
        }

        LmsRoomParticipant::firstOrCreate([
            'room_id' => $this->room->id,
            'student_nim' => $student->nim,
        ]);

        $this->joined = true;
        session()->flash('success', 'Anda berhasil bergabung ke room.');
    }

    public function render()
    {
        if (! $this->room || ! $this->room->id) {
            $this->room = null;
        }

        return view('livewire.holdings.campus.lms.room.lms-room-join', [
            'room' => $this->room,
            'joined' => $this->joined,
        ])->layout('components.sccr-layout');
    }
}
