<?php

namespace App\Livewire\Holdings\Resto\Master\Meja;

use App\Models\Holdings\Resto\Master\Rst_Meja;
use Livewire\Component;

class MejaShow extends Component
{
    public ?Rst_Meja $meja = null;

    public function mount(string $id): void
    {
        $this->meja = Rst_Meja::withTrashed()->find($id);
    }

    public function edit(): void
    {
        $this->dispatch('meja-overlay-close');
        $this->dispatch('meja-open-edit', id: $this->meja->id);
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.meja.meja-show');
    }
}
