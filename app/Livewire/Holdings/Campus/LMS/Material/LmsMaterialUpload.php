<?php

namespace App\Livewire\Holdings\Campus\LMS\Material;

use App\Models\Holdings\Campus\LMS\LearningMaterial;
use App\Models\Holdings\Campus\LMS\LmsRoom;
use App\Traits\ResolvesRoom;
use Livewire\Component;

class LmsMaterialUpload extends Component
{
    use ResolvesRoom;

    public ?LmsRoom $room = null;

    public $title;

    public $description;

    public $type = 'document';

    public $file_path;

    public function mount(?LmsRoom $room = null)
    {
        $this->room = $this->resolveRoom($room);
    }

    public function upload()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:document,video,slide,link',
            'file_path' => 'required|string|max:2048',
        ]);

        if (! $this->room) {
            session()->flash('error', 'Room tidak ditemukan.');

            return;
        }

        LearningMaterial::create([
            'room_id' => $this->room->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'file_path' => $this->file_path,
        ]);

        session()->flash('success', 'Materi berhasil diunggah.');
        $this->reset(['title', 'description', 'type', 'file_path']);
    }

    public function render()
    {
        if (! $this->room) {
            return view('livewire.error.no-room')->layout('components.sccr-layout');
        }

        return view('livewire.holdings.campus.lms.material.lms-material-upload')
            ->layout('components.sccr-layout');
    }
}
