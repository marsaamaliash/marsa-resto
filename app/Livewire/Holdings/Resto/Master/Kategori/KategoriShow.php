<?php

namespace App\Livewire\Holdings\Resto\Master\Kategori;

use App\Models\Holdings\Resto\Master\Rst_MasterKategori;
use Livewire\Component;

class KategoriShow extends Component
{
    public ?Rst_MasterKategori $kategori = null;

    public function mount(string $id): void
    {
        $this->kategori = Rst_MasterKategori::withTrashed()->find($id);
    }

    public function edit(): void
    {
        $this->dispatch('kategori-overlay-close');
        $this->dispatch('kategori-open-edit', id: $this->kategori->id);
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.kategori.kategori-show');
    }
}
