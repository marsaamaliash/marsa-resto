<?php

namespace App\Livewire\Holdings\Resto\Master\Satuan;

use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Livewire\Component;

class SatuanEdit extends Component
{
    public Rst_MasterSatuan $satuan;

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

    public function mount(string $id): void
    {
        $this->satuan = Rst_MasterSatuan::withTrashed()->findOrFail($id);

        $this->name = $this->satuan->name;
        $this->symbols = $this->satuan->symbols;
        $this->type = $this->satuan->type;
        $this->is_active = (bool) $this->satuan->is_active;
    }

    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbols' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'in:weight,volume,unit'],
        ]);

        $this->satuan->update([
            'name' => $this->name,
            'symbols' => $this->symbols,
            'type' => $this->type,
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Unit updated successfully'];

        $this->dispatch('satuan-updated');
        $this->dispatch('satuan-overlay-close');
    }

    public function saveDraft(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbols' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'in:weight,volume,unit'],
        ]);

        $this->satuan->update([
            'name' => $this->name,
            'symbols' => $this->symbols,
            'type' => $this->type,
            'is_active' => false,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Unit draft saved successfully'];

        $this->dispatch('satuan-updated');
        $this->dispatch('satuan-overlay-close');
    }

    public function cancel(): void
    {
        $this->dispatch('satuan-overlay-close');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.satuan.satuan-edit');
    }
}
