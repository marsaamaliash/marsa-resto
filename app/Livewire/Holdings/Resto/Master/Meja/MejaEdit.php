<?php

namespace App\Livewire\Holdings\Resto\Master\Meja;

use App\Models\Holdings\Resto\Master\Rst_Meja;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MejaEdit extends Component
{
    public Rst_Meja $meja;

    public string $table_number = '';

    public int $capacity = 2;

    public string $area = 'indoor';

    public string $status = 'available';

    public ?string $notes = null;

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function mount(string $id): void
    {
        $this->meja = Rst_Meja::withTrashed()->findOrFail($id);

        $this->table_number = $this->meja->table_number;
        $this->capacity = $this->meja->capacity;
        $this->area = $this->meja->area;
        $this->status = $this->meja->status;
        $this->notes = $this->meja->notes;
        $this->is_active = (bool) $this->meja->is_active;
    }

    public function update(): void
    {
        $this->validate([
            'table_number' => ['required', 'string', 'max:50', Rule::unique('sccr_resto.meja', 'table_number')->ignore($this->meja->id)],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'area' => ['required', 'in:indoor,outdoor,vip,smoking,non-smoking'],
            'status' => ['required', 'in:available,occupied,reserved,maintenance'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        $this->meja->update([
            'table_number' => $this->table_number,
            'capacity' => $this->capacity,
            'area' => $this->area,
            'status' => $this->status,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('meja-updated');
        $this->dispatch('meja-overlay-close');
    }

    public function cancel(): void
    {
        $this->dispatch('meja-overlay-close');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.meja.meja-edit');
    }
}
