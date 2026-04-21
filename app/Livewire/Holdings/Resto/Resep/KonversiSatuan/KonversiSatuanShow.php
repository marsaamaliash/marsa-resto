<?php

namespace App\Livewire\Holdings\Resto\Resep\KonversiSatuan;

use App\Models\Holdings\Resto\Resep\Rst_KonversiSatuan;
use Livewire\Component;

class KonversiSatuanShow extends Component
{
    public ?Rst_KonversiSatuan $konversi = null;

    public function mount(string $id): void
    {
        $this->konversi = Rst_KonversiSatuan::withTrashed()
            ->with(['item', 'fromUom', 'toUom'])
            ->find($id);
    }

    public function edit(): void
    {
        $this->dispatch('konversi-satuan-overlay-close');
        $this->dispatch('konversi-satuan-open-edit', id: $this->konversi->id);
    }

    public function render()
    {
        return view('livewire.holdings.resto.resep.konversi-satuan.konversi-satuan-show');
    }
}
