<?php

namespace App\Livewire\Holdings\Resto\Resep\KonversiSatuan;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Models\Holdings\Resto\Resep\Rst_KonversiSatuan;
use Livewire\Component;

class KonversiSatuanCreate extends Component
{
    public string $item_id = '';

    public string $from_uom_id = '';

    public string $to_uom_id = '';

    public string $conversion_factor = '';

    public ?string $notes = '';

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    protected function rules(): array
    {
        return [
            'item_id' => ['required', 'integer'],
            'from_uom_id' => ['required', 'integer', 'different:to_uom_id'],
            'to_uom_id' => ['required', 'integer', 'different:from_uom_id'],
            'conversion_factor' => ['required', 'numeric', 'min:0.0001'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'from_uom_id.different' => 'From Unit must be different from To Unit',
            'to_uom_id.different' => 'To Unit must be different from From Unit',
        ];
    }

    public function store(): void
    {
        $validated = $this->validate();

        $exists = Rst_KonversiSatuan::where('item_id', $this->item_id)
            ->where('from_uoms_id', $this->from_uom_id)
            ->where('to_uoms_id', $this->to_uom_id)
            ->exists();

        if ($exists) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Conversion already exists for this combination'];

            return;
        }

        Rst_KonversiSatuan::create([
            'item_id' => $validated['item_id'],
            'from_uoms_id' => $validated['from_uom_id'],
            'to_uoms_id' => $validated['to_uom_id'],
            'conversion_factor' => $validated['conversion_factor'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->dispatch('konversi-satuan-created');
        $this->dispatch('konversi-satuan-overlay-close');

        $this->reset(['item_id', 'from_uom_id', 'to_uom_id', 'conversion_factor', 'notes']);
    }

    public function saveDraft(): void
    {
        $validated = $this->validate();

        $exists = Rst_KonversiSatuan::where('item_id', $this->item_id)
            ->where('from_uoms_id', $this->from_uom_id)
            ->where('to_uoms_id', $this->to_uom_id)
            ->exists();

        if ($exists) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Conversion already exists for this combination'];

            return;
        }

        Rst_KonversiSatuan::create([
            'item_id' => $validated['item_id'],
            'from_uoms_id' => $validated['from_uom_id'],
            'to_uoms_id' => $validated['to_uom_id'],
            'conversion_factor' => $validated['conversion_factor'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => false,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Unit conversion draft saved successfully'];

        $this->dispatch('konversi-satuan-created');
        $this->dispatch('konversi-satuan-overlay-close');

        $this->reset(['item_id', 'from_uom_id', 'to_uom_id', 'conversion_factor', 'notes']);
    }

    public function cancel(): void
    {
        $this->dispatch('konversi-satuan-overlay-close');
    }

    public function render()
    {
        $items = Rst_MasterItem::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $uoms = Rst_MasterSatuan::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        return view('livewire.holdings.resto.resep.konversi-satuan.konversi-satuan-create', [
            'items' => $items,
            'uoms' => $uoms,
        ]);
    }
}
