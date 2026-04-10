<?php

namespace App\Livewire\Holdings\Resto\Movement\Internal;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
use App\Models\Holdings\Resto\Movement\Rst_MovementItem;
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

    public array $availableItems = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function mount(): void
    {
        $this->loadLocations();
        $this->loadAvailableItems();
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

    private function loadAvailableItems(): void
    {
        $this->availableItems = Rst_MasterItem::where('is_stockable', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'uom_id' => $item->uom_id,
                'uom_name' => $item->uom?->name ?? '',
                'uom_symbols' => $item->uom?->symbols ?? '',
            ])
            ->toArray();
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

    public function onFromLocationChanged(): void
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
            'from_location_id.required' => 'Lokasi Asal wajib dipilih.',
            'to_location_id.required' => 'Lokasi Tujuan wajib dipilih.',
        ]);

        if ($this->from_location_id === $this->to_location_id) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Lokasi Asal dan Tujuan tidak boleh sama.'];

            return;
        }

        $validItems = array_filter($this->items, fn ($item) => $item['item_id'] > 0 && $item['qty'] > 0);

        if (empty($validItems)) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Minimal pilih 1 item dengan qty > 0.'];

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
                $errors[] = "Qty {$itemName} melebihi stok tersedia ({$available}).";
            }
        }

        if (! empty($errors)) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => implode(' ', $errors)];

            return;
        }

        $movement = Rst_Movement::create([
            'from_location_id' => $this->from_location_id,
            'to_location_id' => $this->to_location_id,
            'pic_name' => $this->pic_name,
            'type' => 'internal_transfer',
            'status' => 'requested',
            'remark' => $this->remark ?: null,
        ]);

        foreach ($validItems as $item) {
            Rst_MovementItem::create([
                'movement_id' => $movement->id,
                'item_id' => $item['item_id'],
                'qty' => $item['qty'],
                'uom_id' => $item['uom_id'],
                'remark' => $item['remark'] ?: null,
            ]);
        }

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Request Movement berhasil dibuat.'];
        $this->dispatch('movement-internal-created');
        $this->dispatch('movement-internal-overlay-close');

        $this->reset(['from_location_id', 'to_location_id', 'pic_name', 'remark', 'items']);
        $this->addItemRow();
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
