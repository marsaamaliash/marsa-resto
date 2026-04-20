<?php

namespace App\Livewire\Holdings\Resto\Master\Satuan;

use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Livewire\Component;

class SatuanShow extends Component
{
    public ?Rst_MasterSatuan $satuan = null;

    public function mount(string $id): void
    {
        $this->satuan = Rst_MasterSatuan::withTrashed()->find($id);
    }

    public function edit(): void
    {
        $this->dispatch('satuan-overlay-close');
        $this->dispatch('satuan-open-edit', id: $this->satuan->id);
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.satuan.satuan-show');
    }
}
