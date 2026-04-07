<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi;

use App\Models\Holding;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class InvMasterLokasiCreate extends Component
{
    public string $holdingKode = '';

    public string $lokasiKode = '';

    public string $namaLokasi = '';

    public function save(): void
    {
        $this->validate([
            'holdingKode' => ['required', 'string', 'size:2'],
            'lokasiKode' => [
                'required', 'string', 'size:2',
                Rule::unique('inv_lokasi', 'kode')->where(fn ($q) => $q->where('holding_kode', $this->holdingKode)),
            ],
            'namaLokasi' => ['required', 'string', 'max:150'],
        ], [
            'lokasiKode.unique' => 'Kode lokasi sudah terdaftar di holding ini.',
        ]);

        $ab = strtoupper(trim($this->holdingKode));
        $cd = strtoupper(trim($this->lokasiKode));
        $nama = trim($this->namaLokasi);

        DB::table('inv_lokasi')->insert([
            'holding_kode' => $ab,
            'kode' => $cd,
            'lokasi' => $nama,
        ]);

        // ✅ KIRIM EVENT KE TABLE (parent) untuk close overlay + toast + refresh
        // rowKey format: "AB.CD"
        $this->dispatch('inv-master-lokasi-created', rowKey: ($ab.'.'.$cd));

        // Optional: reset form biar kalau overlay dibuka lagi kosong
        $this->reset(['lokasiKode', 'namaLokasi']);
    }

    public function render()
    {
        $holdingOptions = Holding::query()
            ->whereNotNull('inv_code')
            ->orderBy('inv_code')
            ->get()
            ->mapWithKeys(fn ($h) => [$h->inv_code => $h->inv_code.' - '.$h->alias])
            ->toArray();

        return view('livewire.holdings.hq.sdm.rt.inventaris.master.lokasi.inv-master-lokasi-create', [
            'holdingOptions' => $holdingOptions,
        ]);
    }
}
