<?php

namespace App\Livewire\Holdings\Resto\Master\Item;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterKategori;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Livewire\Component;

class ItemCreate extends Component
{
    public string $name = '';

    public string $sku = '';

    public string $description = '';

    public ?int $category_id = null;

    public ?int $uom_id = null;

    public string $min_stock = '0';

    public bool $is_active = true;

    public bool $is_stockable = true;

    public bool $has_batch = false;

    public bool $has_expiry = false;

    public string $type = 'raw';

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public array $categories = [];

    public array $uoms = [];

    public array $typeOptions = [
        ['value' => 'raw', 'label' => 'Raw Material'],
        ['value' => 'prep', 'label' => 'Semi Finished'],
    ];

    public function mount(): void
    {
        $this->categories = Rst_MasterKategori::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])
            ->toArray();

        $this->uoms = Rst_MasterSatuan::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($u) => ['value' => $u->id, 'label' => $u->name.' ('.$u->symbols.')'])
            ->toArray();
    }

    public function store(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:sccr_resto.items,name'],
            'sku' => ['required', 'string', 'max:255', 'unique:sccr_resto.items,sku'],
            'description' => ['nullable', 'string', 'max:65535'],
            'category_id' => ['required', 'integer', 'exists:sccr_resto.categories,id'],
            'uom_id' => ['required', 'integer', 'exists:sccr_resto.uoms,id'],
            'type' => ['required', 'in:raw,prep'],
        ];

        if ($this->is_stockable) {
            $rules['min_stock'] = ['required', 'numeric', 'min:0'];
        }

        $this->validate($rules);

        Rst_MasterItem::create([
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'uom_id' => $this->uom_id,
            'min_stock' => $this->is_stockable ? $this->min_stock : 0,
            'is_active' => $this->is_active,
            'is_stockable' => $this->is_stockable,
            'has_batch' => $this->is_stockable ? $this->has_batch : false,
            'has_expiry' => $this->is_stockable ? $this->has_expiry : false,
            'type' => $this->type,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Item added successfully'];

        $this->dispatch('item-created');
        $this->dispatch('item-overlay-close');

        $this->resetForm();
    }

    public function saveDraft(): void
    {
        $rules = [
            'name' => ['required', 'string', 'max:255', 'unique:sccr_resto.items,name'],
            'sku' => ['required', 'string', 'max:255', 'unique:sccr_resto.items,sku'],
            'category_id' => ['required', 'integer', 'exists:sccr_resto.categories,id'],
            'uom_id' => ['required', 'integer', 'exists:sccr_resto.uoms,id'],
            'type' => ['required', 'in:raw,prep'],
        ];

        if ($this->is_stockable) {
            $rules['min_stock'] = ['required', 'numeric', 'min:0'];
        }

        $this->validate($rules);

        Rst_MasterItem::create([
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'uom_id' => $this->uom_id,
            'min_stock' => $this->is_stockable ? $this->min_stock : 0,
            'is_active' => false,
            'is_stockable' => $this->is_stockable,
            'has_batch' => $this->is_stockable ? $this->has_batch : false,
            'has_expiry' => $this->is_stockable ? $this->has_expiry : false,
            'type' => $this->type,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Item draft saved successfully'];

        $this->dispatch('item-created');
        $this->dispatch('item-overlay-close');

        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->dispatch('item-overlay-close');
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'sku', 'description', 'category_id', 'uom_id', 'min_stock']);
        $this->is_active = true;
        $this->is_stockable = true;
        $this->has_batch = false;
        $this->has_expiry = false;
        $this->type = 'raw';
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.item.item-create');
    }
}
