<?php

namespace App\Livewire\Holdings\Resto\Master\Lokasi;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Livewire\Component;

class LokasiShow extends Component
{
    public ?Rst_MasterLokasi $lokasi = null;

    public function mount(string $id): void
    {
        $this->lokasi = Rst_MasterLokasi::withTrashed()->find($id);
    }

    public function edit(): void
    {
        $this->dispatch('lokasi-overlay-close');
        $this->dispatch('lokasi-open-edit', id: $this->lokasi->id);
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.lokasi.lokasi-show');
    }
}
