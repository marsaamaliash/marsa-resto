<?php

namespace App\Livewire\Holdings\Resto\Movement\Internal;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Services\Resto\StockMovementService;
use Livewire\Component;

class MovementInternalCreate extends Component
{
    public int $from_location_id = 0;

    public int $to_location_id = 0;

    public string $pic_name = '';

    public string $remark = '';

    public array $items = [];

    public array $fromLocations = [];

    public array $toLocations = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function mount(): void
    {
        $this->loadLocations();
        $this->addItemRow();
    }

    private function loadLocations(): void
    {
        $this->fromLocations = Rst_MasterLokasi::where('type', 'warehouse')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($loc) => ['id' => $loc->id, 'name' => $loc->name])
            ->toArray();

        $this->toLocations = Rst_MasterLokasi::where('type', 'kitchen')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($loc) => ['id' => $loc->id, 'name' => $loc->name])
            ->toArray();
    }

    public function getAvailableItemsProperty(): array
    {
        $items = Rst_MasterItem::where('is_stockable', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $result = $items->map(function ($item) {
            $availableQty = 0;

            if ($this->from_location_id > 0) {
                $balance = Rst_StockBalance::where('item_id', $item->id)
                    ->where('location_id', $this->from_location_id)
                    ->first();
                $availableQty = $balance?->qty_available ?? 0;
            }

            return [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'uom_id' => $item->uom_id,
                'uom_name' => $item->uom?->name ?? '',
                'uom_symbols' => $item->uom?->symbols ?? '',
                'available_qty' => $availableQty,
            ];
        })->toArray();

        return $result;
    }

    public function addItemRow(): void
    {
        $this->items[] = [
            'item_id' => 0,
            'qty' => 0,
            'uom_id' => 0,
            'remark' => '',
            'available_qty' => 0,
        ];
    }

    public function removeItemRow(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    public function updatedFromLocationId(): void
    {
        $this->updateAvailableStock();
    }

    public function onItemChanged(int $index): void
    {
        if (isset($this->items[$index])) {
            $itemId = $this->items[$index]['item_id'];
            $item = collect($this->availableItems)->firstWhere('id', $itemId);
            if ($item) {
                $this->items[$index]['uom_id'] = $item['uom_id'];
            }
            $this->updateAvailableStock();
        }
    }

    private function updateAvailableStock(): void
    {
        if ($this->from_location_id > 0) {
            foreach ($this->items as $index => $item) {
                if ($item['item_id'] > 0) {
                    $balance = Rst_StockBalance::where('item_id', $item['item_id'])
                        ->where('location_id', $this->from_location_id)
                        ->first();
                    $this->items[$index]['available_qty'] = $balance?->qty_available ?? 0;
                } else {
                    $this->items[$index]['available_qty'] = 0;
                }
            }
        }
    }

    public function store(): void
    {
        $this->validate([
            'from_location_id' => ['required', 'integer', 'min:1'],
            'to_location_id' => ['required', 'integer', 'min:1'],
        ], [
            'from_location_id.required' => 'Source location is required.',
            'to_location_id.required' => 'Destination location is required.',
        ]);

        if ($this->from_location_id === $this->to_location_id) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Source and destination locations cannot be the same.'];

            return;
        }

        $validItems = array_filter($this->items, fn ($item) => $item['item_id'] > 0 && $item['qty'] > 0);

        if (empty($validItems)) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select at least 1 item with qty > 0.'];

            return;
        }

        $errors = [];
        foreach ($validItems as $index => $item) {
            $balance = Rst_StockBalance::where('item_id', $item['item_id'])
                ->where('location_id', $this->from_location_id)
                ->first();

            $available = $balance?->qty_available ?? 0;
            if ($item['qty'] > $available) {
                $foundItem = collect($this->availableItems)->firstWhere('id', $item['item_id']);
                $itemName = $foundItem['name'] ?? 'Item';
                $errors[] = "Qty {$itemName} exceeds available stock ({$available}).";
            }
        }

        if (! empty($errors)) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => implode(' ', $errors)];

            return;
        }

        try {
            $itemsData = array_map(fn ($item) => [
                'item_id' => $item['item_id'],
                'qty' => $item['qty'],
                'notes' => $item['remark'],
            ], $validItems);

            StockMovementService::createMovement(
                fromLocationId: $this->from_location_id,
                toLocationId: $this->to_location_id,
                items: $itemsData,
                notes: $this->remark
            );

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement request created successfully.'];
            $this->dispatch('movement-internal-created');
            $this->dispatch('movement-internal-overlay-close');

            $this->reset(['from_location_id', 'to_location_id', 'pic_name', 'remark', 'items']);
            $this->addItemRow();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function cancel(): void
    {
        $this->dispatch('movement-internal-overlay-close');
    }

    public function render()
    {
        return view('livewire.holdings.resto.movement.internal.movement-internal-create');
    }
}
