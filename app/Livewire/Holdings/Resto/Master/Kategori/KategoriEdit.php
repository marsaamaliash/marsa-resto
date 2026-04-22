<?php

namespace App\Livewire\Holdings\Resto\Master\Kategori;

use App\Models\Holdings\Resto\Master\Rst_MasterKategori;
use Livewire\Component;

class KategoriEdit extends Component
{
    public Rst_MasterKategori $kategori;

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function mount(string $id): void
    {
        $this->kategori = Rst_MasterKategori::withTrashed()->findOrFail($id);

        $this->name = $this->kategori->name;
        $this->description = $this->kategori->description ?? '';
        $this->is_active = (bool) $this->kategori->is_active;
    }

    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
        ]);

        $this->kategori->update([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Category updated successfully'];

        $this->dispatch('kategori-updated');
        $this->dispatch('kategori-overlay-close');
    }

    public function saveDraft(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
        ]);

        $this->kategori->update([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => false,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Category draft saved successfully'];

        $this->dispatch('kategori-updated');
        $this->dispatch('kategori-overlay-close');
    }

    public function cancel(): void
    {
        $this->dispatch('kategori-overlay-close');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.kategori.kategori-edit');
    }
}
