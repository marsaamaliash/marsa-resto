<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi;

use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inv_Lokasi_List;
use Livewire\Component;

class InvMasterLokasiShow extends Component
{
    public string $rowKey = '';

    public ?Inv_Lokasi_List $row = null;

    public function mount(string $rowKey): void
    {
        $this->rowKey = $rowKey;

        [$ab, $cd] = array_pad(explode('.', $rowKey, 2), 2, '');
        $ab = strtoupper(trim($ab));
        $cd = strtoupper(trim($cd));

        if ($ab === '' || $cd === '') {
            abort(404, 'RowKey tidak valid.');
        }

        $this->row = Inv_Lokasi_List::query()
            ->where('holding_kode', $ab)
            ->where('lokasi_kode', $cd)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.rt.inventaris.master.lokasi.inv-master-lokasi-show');
    }
}
