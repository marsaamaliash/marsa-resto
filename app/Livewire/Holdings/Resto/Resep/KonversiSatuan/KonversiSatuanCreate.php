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

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    protected function rules(): array
    {
        return [
            'item_id' => ['required', 'integer'],
            'from_uom_id' => ['required', 'integer', 'different:to_uom_id'],
            'to_uom_id' => ['required', 'integer', 'different:from_uom_id'],
            'conversion_factor' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    protected function messages(): array
    {
        return [
            'from_uom_id.different' => 'Dari Satuan harus berbeda dengan Ke Satuan',
            'to_uom_id.different' => 'Ke Satuan harus berbeda dengan Dari Satuan',
        ];
    }

    public function store(): void
    {
        $this->validate();

        $exists = Rst_KonversiSatuan::where('item_id', $this->item_id)
            ->where('from_uoms_id', $this->from_uom_id)
            ->where('to_uoms_id', $this->to_uom_id)
            ->exists();

        if ($exists) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Konversi sudah ada untuk kombinasi ini'];

            return;
        }

        Rst_KonversiSatuan::create([
            'item_id' => $this->item_id,
            'from_uoms_id' => $this->from_uom_id,
            'to_uoms_id' => $this->to_uom_id,
            'conversion_factor' => $this->conversion_factor,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Konversi Satuan berhasil ditambahkan'];

        $this->dispatch('konversi-satuan-created');
        $this->dispatch('konversi-satuan-overlay-close');

        $this->reset(['item_id', 'from_uom_id', 'to_uom_id', 'conversion_factor']);
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
