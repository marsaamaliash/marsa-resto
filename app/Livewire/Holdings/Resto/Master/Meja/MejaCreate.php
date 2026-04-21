<?php

namespace App\Livewire\Holdings\Resto\Master\Meja;

use App\Models\Holdings\Resto\Master\Rst_Meja;
use Livewire\Component;

class MejaCreate extends Component
{
    public string $table_number = '';

    public int $capacity = 2;

    public string $area = 'indoor';

    public string $status = 'available';

    public ?string $notes = null;

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function store(): void
    {
        $this->validate([
            'table_number' => ['required', 'string', 'max:50', 'unique:sccr_resto.meja,table_number'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'area' => ['required', 'in:indoor,outdoor,vip,smoking,non-smoking'],
            'status' => ['required', 'in:available,occupied,reserved,maintenance'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        Rst_Meja::create([
            'table_number' => $this->table_number,
            'capacity' => $this->capacity,
            'area' => $this->area,
            'status' => $this->status,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('meja-created');
        $this->dispatch('meja-overlay-close');

        $this->reset(['table_number', 'notes']);
        $this->capacity = 2;
        $this->area = 'indoor';
        $this->status = 'available';
        $this->is_active = true;
    }

    public function saveDraft(): void
    {
        $this->validate([
            'table_number' => ['required', 'string', 'max:50', 'unique:sccr_resto.meja,table_number'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'area' => ['required', 'in:indoor,outdoor,vip,smoking,non-smoking'],
            'status' => ['required', 'in:available,occupied,reserved,maintenance'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        Rst_Meja::create([
            'table_number' => $this->table_number,
            'capacity' => $this->capacity,
            'area' => $this->area,
            'status' => $this->status,
            'notes' => $this->notes,
            'is_active' => false,
        ]);

        $this->dispatch('meja-created');
        $this->dispatch('meja-overlay-close');

        $this->reset(['table_number', 'notes']);
        $this->capacity = 2;
        $this->area = 'indoor';
        $this->status = 'available';
        $this->is_active = true;
    }

    public function cancel(): void
    {
        $this->dispatch('close-overlay');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.meja.meja-create');
    }
}
