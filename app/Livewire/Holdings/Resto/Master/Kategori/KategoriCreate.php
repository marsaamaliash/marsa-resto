<?php

namespace App\Livewire\Holdings\Resto\Master\Kategori;

use App\Models\Holdings\Resto\Master\Rst_MasterKategori;
use Livewire\Component;

class KategoriCreate extends Component
{
    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function store(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
        ]);

        Rst_MasterKategori::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Category added successfully'];

        $this->dispatch('kategori-created');
        $this->dispatch('kategori-overlay-close');

        $this->reset(['name', 'description']);

        $this->is_active = true;
    }

    public function saveDraft(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
        ]);

        Rst_MasterKategori::create([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => false,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Category draft saved successfully'];

        $this->dispatch('kategori-created');
        $this->dispatch('kategori-overlay-close');

        $this->reset(['name', 'description']);

        $this->is_active = true;
    }

    public function cancel(): void
    {
        $this->dispatch('close-overlay');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.kategori.kategori-create');
    }
}
