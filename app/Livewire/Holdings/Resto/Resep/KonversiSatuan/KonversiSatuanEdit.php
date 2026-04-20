<?php

namespace App\Livewire\Holdings\Resto\Resep\KonversiSatuan;

use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use App\Models\Holdings\Resto\Resep\Rst_KonversiSatuan;
use Livewire\Component;

class KonversiSatuanEdit extends Component
{
    public Rst_KonversiSatuan $konversi;

    public string $item_id = '';

    public string $from_uom_id = '';

    public string $to_uom_id = '';

    public string $conversion_factor = '';

    public ?string $notes = '';

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function mount(string $id): void
    {
        $this->konversi = Rst_KonversiSatuan::withTrashed()->findOrFail($id);

        $this->item_id = (string) $this->konversi->item_id;
        $this->from_uom_id = (string) $this->konversi->from_uoms_id;
        $this->to_uom_id = (string) $this->konversi->to_uoms_id;
        $this->conversion_factor = (string) $this->konversi->conversion_factor;
        $this->notes = $this->konversi->notes ?? '';
    }

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
            'from_uom_id.different' => 'Dari Satuan harus berbeda dengan Ke Satuan',
            'to_uom_id.different' => 'Ke Satuan harus berbeda dengan Dari Satuan',
        ];
    }

    public function update(): void
    {
        $validated = $this->validate();

        $duplicate = Rst_KonversiSatuan::where('item_id', $this->item_id)
            ->where('from_uoms_id', $this->from_uom_id)
            ->where('to_uoms_id', $this->to_uom_id)
            ->where('id', '!=', $this->konversi->id)
            ->exists();

        if ($duplicate) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Konversi sudah ada untuk kombinasi ini'];

            return;
        }

        $this->konversi->update([
            'item_id' => $validated['item_id'],
            'from_uoms_id' => $validated['from_uom_id'],
            'to_uoms_id' => $validated['to_uom_id'],
            'conversion_factor' => $validated['conversion_factor'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->dispatch('konversi-satuan-updated');
        $this->dispatch('konversi-satuan-overlay-close');
    }

    public function saveDraft(): void
    {
        $validated = $this->validate();

        $duplicate = Rst_KonversiSatuan::where('item_id', $this->item_id)
            ->where('from_uoms_id', $this->from_uom_id)
            ->where('to_uoms_id', $this->to_uom_id)
            ->where('id', '!=', $this->konversi->id)
            ->exists();

        if ($duplicate) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Konversi sudah ada untuk kombinasi ini'];

            return;
        }

        $this->konversi->update([
            'item_id' => $validated['item_id'],
            'from_uoms_id' => $validated['from_uom_id'],
            'to_uoms_id' => $validated['to_uom_id'],
            'conversion_factor' => $validated['conversion_factor'],
            'notes' => $validated['notes'] ?? null,
            'is_active' => false,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Draft Konversi Satuan berhasil disimpan'];

        $this->dispatch('konversi-satuan-updated');
        $this->dispatch('konversi-satuan-overlay-close');
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

        return view('livewire.holdings.resto.resep.konversi-satuan.konversi-satuan-edit', [
            'items' => $items,
            'uoms' => $uoms,
        ]);
    }
}
