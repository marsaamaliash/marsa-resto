<?php

namespace App\Livewire\Holdings\Resto\Master\Satuan;

use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Livewire\Component;

class SatuanCreate extends Component
{
    public string $name = '';

    public string $symbols = '';

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function store(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbols' => ['required', 'string', 'max:50'],
        ]);

        Rst_MasterSatuan::create([
            'name' => $this->name,
            'symbols' => $this->symbols,
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Satuan berhasil ditambahkan'];

        $this->dispatch('satuan-created');
        $this->dispatch('satuan-overlay-close');

        $this->reset(['name', 'symbols']);
        $this->is_active = true;
    }

    public function cancel(): void
    {
        $this->dispatch('close-overlay');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.satuan.satuan-create');
    }
}
