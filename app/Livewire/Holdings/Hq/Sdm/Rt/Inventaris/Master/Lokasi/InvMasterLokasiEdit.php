<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi;

use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inv_Lokasi_List;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InvMasterLokasiEdit extends Component
{
    public string $rowKey = '';

    public string $holdingKode = '';

    public string $namaHolding = '';

    public string $lokasiKode = '';

    public string $namaLokasi = '';

    public function mount(string $rowKey): void
    {
        $this->rowKey = $rowKey;

        [$ab, $cd] = array_pad(explode('.', $rowKey, 2), 2, '');
        $ab = strtoupper(trim($ab));
        $cd = strtoupper(trim($cd));

        $row = Inv_Lokasi_List::query()
            ->where('holding_kode', $ab)
            ->where('lokasi_kode', $cd)
            ->firstOrFail();

        $this->holdingKode = (string) $row->holding_kode;
        $this->namaHolding = (string) ($row->nama_holding ?? '');
        $this->lokasiKode = (string) $row->lokasi_kode;
        $this->namaLokasi = (string) $row->nama_lokasi;
    }

    public function save(): void
    {
        if (! auth()->user()?->hasPermission('INV_MASTER_LOKASI_UPDATE')) {
            $this->addError('namaLokasi', 'Tidak punya izin update Master Lokasi.');

            return;
        }

        $this->validate([
            'namaLokasi' => ['required', 'string', 'max:150'],
        ]);

        DB::table('inv_lokasi')
            ->where('holding_kode', $this->holdingKode)
            ->where('kode', $this->lokasiKode)
            ->update([
                'lokasi' => $this->namaLokasi,
            ]);

        // ✅ Table yang handle close overlay + toast
        $this->dispatch('inv-master-lokasi-updated', rowKey: $this->holdingKode.'.'.$this->lokasiKode);
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.rt.inventaris.master.lokasi.inv-master-lokasi-edit');
    }
}
