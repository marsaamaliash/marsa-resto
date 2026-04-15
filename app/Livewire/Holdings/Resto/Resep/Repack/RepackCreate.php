<?php

namespace App\Livewire\Holdings\Resto\Resep\Repack;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterKategori;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Services\Resto\StockRepackService;
use Livewire\Attributes\On;
use Livewire\Component;

class RepackCreate extends Component
{
    public string $location_id = '';

    public string $source_item_id = '';

    public string $target_item_id = '';

    public string $qty_source_taken = '';

    public string $multiplier = '';

    public string $notes = '';

    public bool $showNewItemModal = false;

    public string $newItemName = '';

    public string $newItemSku = '';

    public string $newItemDescription = '';

    public string $newItemCategoryId = '';

    public string $newItemMinStock = '0';

    public string $newItemType = 'raw';

    public string $newItemUomId = '';

    public array $categories = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    protected function rules(): array
    {
        return [
            'location_id' => ['required', 'integer'],
            'source_item_id' => ['required', 'integer', 'different:target_item_id'],
            'target_item_id' => ['required', 'integer', 'different:source_item_id'],
            'qty_source_taken' => ['required', 'numeric', 'min:0.0001'],
            'multiplier' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'source_item_id.different' => 'Item Sumber harus berbeda dengan Item Target',
            'target_item_id.different' => 'Item Target harus berbeda dengan Item Sumber',
        ];
    }

    public function store(): void
    {
        $this->validate();

        try {
            $service = app(StockRepackService::class);
            $service->executeRepack([
                'location_id' => $this->location_id,
                'source_item_id' => $this->source_item_id,
                'target_item_id' => $this->target_item_id,
                'qty_source_taken' => $this->qty_source_taken,
                'multiplier' => $this->multiplier,
                'notes' => $this->notes ?: null,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Repack Stok berhasil ditambahkan'];

            $this->dispatch('repack-created');
            $this->dispatch('repack-overlay-close');

            $this->reset();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function cancel(): void
    {
        $this->dispatch('repack-overlay-close');
    }

    public function mount(): void
    {
        $this->categories = Rst_MasterKategori::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])
            ->toArray();
    }

    public function openNewItemModal(): void
    {
        $this->showNewItemModal = true;
        $this->newItemName = '';
        $this->newItemSku = '';
        $this->newItemDescription = '';
        $this->newItemCategoryId = '';
        $this->newItemMinStock = '0';
        $this->newItemType = 'raw';
        $this->newItemUomId = '';
    }

    public function closeNewItemModal(): void
    {
        $this->showNewItemModal = false;
    }

    #[On('refresh-items')]
    public function refreshItems(): void
    {
        $this->dispatch('refresh-items');
    }

    protected function newItemRules(): array
    {
        return [
            'newItemName' => ['required', 'string', 'max:255'],
            'newItemSku' => ['required', 'string', 'max:255', 'unique:sccr_resto.items,sku'],
            'newItemDescription' => ['nullable', 'string', 'max:65535'],
            'newItemCategoryId' => ['required', 'integer', 'exists:sccr_resto.categories,id'],
            'newItemMinStock' => ['nullable', 'numeric', 'min:0'],
            'newItemType' => ['required', 'string', 'in:raw,prep,menu'],
            'newItemUomId' => ['required', 'integer', 'exists:sccr_resto.uoms,id'],
        ];
    }

    public function saveNewItem(): void
    {
        $this->validate($this->newItemRules());

        $item = Rst_MasterItem::create([
            'name' => $this->newItemName,
            'sku' => $this->newItemSku,
            'description' => $this->newItemDescription ?: null,
            'category_id' => $this->newItemCategoryId,
            'uom_id' => $this->newItemUomId,
            'min_stock' => $this->newItemMinStock ?: 0,
            'type' => $this->newItemType,
            'is_active' => true,
            'is_stockable' => true,
        ]);

        $this->showNewItemModal = false;
        $this->target_item_id = (string) $item->id;
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Item berhasil ditambahkan'];
        $this->dispatch('item-created');
    }

    public function render()
    {
        $locations = Rst_MasterLokasi::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $items = Rst_MasterItem::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $itemUoms = Rst_MasterItem::where('is_active', true)
            ->pluck('uom_id', 'id')
            ->toArray();

        $uoms = Rst_MasterSatuan::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $categories = Rst_MasterKategori::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])
            ->toArray();

        return view('livewire.holdings.resto.resep.repack.repack-create', [
            'locations' => $locations,
            'items' => $items,
            'itemUoms' => $itemUoms,
            'uoms' => $uoms,
            'categories' => $categories,
        ])->layout('components.sccr-layout');
    }
}
