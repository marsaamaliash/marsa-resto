<?php

namespace App\Livewire\Holdings\Resto\Master\Lokasi;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LokasiEdit extends Component
{
    public Rst_MasterLokasi $lokasi;

    public string $name = '';

    public string $code = '';

    public string $type = '';

    public string $pic_name = '';

    public ?string $notes = null;

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function mount(string $id): void
    {
        $this->lokasi = Rst_MasterLokasi::withTrashed()->findOrFail($id);

        $this->name = $this->lokasi->name;
        $this->code = $this->lokasi->code ?? '';
        $this->type = $this->lokasi->type;
        $this->pic_name = $this->lokasi->pic_name ?? '';
        $this->notes = $this->lokasi->notes;
        $this->is_active = (bool) $this->lokasi->is_active;
    }

    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('sccr_resto.locations', 'code')->ignore($this->lokasi->id)],
            'type' => ['required', 'string', 'max:50'],
            'pic_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ]);

        $this->lokasi->update([
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'pic_name' => $this->pic_name,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('lokasi-updated');
        $this->dispatch('lokasi-overlay-close');
    }

    public function cancel(): void
    {
        $this->dispatch('lokasi-overlay-close');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.lokasi.lokasi-edit');
    }
}
