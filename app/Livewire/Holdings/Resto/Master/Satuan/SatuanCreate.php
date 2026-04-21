<?php

namespace App\Livewire\Holdings\Resto\Master\Satuan;

use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Livewire\Component;

class SatuanCreate extends Component
{
    public string $name = '';

    public string $symbols = '';

    public ?string $type = null;

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public array $typeOptions = [
        ['value' => 'weight', 'label' => 'Weight'],
        ['value' => 'volume', 'label' => 'Volume'],
        ['value' => 'unit', 'label' => 'Unit'],
    ];

    public function store(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbols' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'in:weight,volume,unit'],
        ]);

        Rst_MasterSatuan::create([
            'name' => $this->name,
            'symbols' => $this->symbols,
            'type' => $this->type,
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Unit added successfully'];

        $this->dispatch('satuan-created');
        $this->dispatch('satuan-overlay-close');

        $this->reset(['name', 'symbols', 'type']);
        $this->is_active = true;
    }

    public function saveDraft(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbols' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'in:weight,volume,unit'],
        ]);

        Rst_MasterSatuan::create([
            'name' => $this->name,
            'symbols' => $this->symbols,
            'type' => $this->type,
            'is_active' => false,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Unit draft saved successfully'];

        $this->dispatch('satuan-created');
        $this->dispatch('satuan-overlay-close');

        $this->reset(['name', 'symbols', 'type']);
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
