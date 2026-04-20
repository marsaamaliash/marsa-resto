<?php

namespace App\Livewire\Holdings\Resto\Master\Lokasi;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Livewire\Component;

class LokasiCreate extends Component
{
    public string $name = '';

    public string $code = '';

    public string $type = '';

    public string $pic_name = '';

    public ?string $notes = null;

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function store(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:sccr_resto.locations,code'],
            'type' => ['required', 'string', 'max:50'],
            'pic_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        Rst_MasterLokasi::create([
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'pic_name' => $this->pic_name,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('lokasi-created');
        $this->dispatch('lokasi-overlay-close');

        $this->reset(['name', 'code', 'type', 'pic_name', 'notes']);
        $this->is_active = true;
    }

    public function cancel(): void
    {
        $this->dispatch('close-overlay');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.lokasi.lokasi-create');
    }
}
