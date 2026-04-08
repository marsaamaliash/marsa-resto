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

    public string $address = '';

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function store(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['unique:sccr_resto.locations,code', 'string', 'max:50'],
            'type' => ['required', 'string', 'max:50'],
            'pic_name' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:50'],

        ]);

        Rst_MasterLokasi::create([
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'pic_name' => $this->pic_name,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Lokasi berhasil ditambahkan'];

        $this->dispatch('lokasi-created');
        $this->dispatch('lokasi-overlay-close');

        $this->reset(['name', 'code', 'type',  'pic_name', 'address']);
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
