<?php

namespace App\Livewire\Holdings\Resto\Master\Item;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use Livewire\Component;

class ItemShow extends Component
{
    public ?Rst_MasterItem $item = null;

    public function mount(string $id): void
    {
        $this->item = Rst_MasterItem::withTrashed()->with(['category', 'uom'])->find($id);
    }

    public function edit(): void
    {
        $this->dispatch('item-overlay-close');
        $this->dispatch('item-open-edit', id: $this->item->id);
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.item.item-show');
    }
}
