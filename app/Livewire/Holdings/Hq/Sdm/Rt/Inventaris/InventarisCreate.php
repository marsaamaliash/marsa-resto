<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris;

use Livewire\Component;
use Livewire\Attributes\Computed;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Holding;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\{
    Inventaris,          // TABLE
    InventarisList,      // VIEW v_inventaris_lengkap (untuk preview no_urut)
    Inv_Jenis_Barang,    // TABLE
    Inv_Lokasi,          // TABLE (hard validation)
    Inv_Ruangan,         // TABLE (hard validation)
    Inv_Lokasi_List,     // VIEW dropdown lokasi
    Inv_Ruangan_List     // VIEW dropdown ruangan
};

class InventarisCreate extends Component
{
    public bool $standalone = false;

    // === props dari overlay (biar sync dengan inventaris-table.blade.php) ===
    public ?string $holdingKode = null;
    public ?string $lokasiKode = null;
    public ?string $ruanganKode = null;

    // FORM
    public ?string $holding_kode = null;
    public ?string $lokasi_kode = null;
    public ?string $ruangan_kode = null;
    public ?string $jenis_barang_kode = null;

    public string $nama_barang = '';
    public ?string $description = null;

    public int $qty = 1;
    public string $bulan = '00';
    public string $tahun = '00';

    public int $no_urut_terakhir = 0;
    public int $no_urut_mulai = 0;

    public bool $showPrintConfirm = false;
    public array $lastCreatedLabels = [];

    /**
     * NOTE:
     * - overlay create dari table bisa mengirim prefill: holdingKode/lokasiKode/ruanganKode
     * - route standalone tidak mengirim parameter
     */
    public function mount(?string $holdingKode = null, ?string $lokasiKode = null, ?string $ruanganKode = null): void
    {
        $this->standalone = request()->route()?->getName() === 'holdings.hq.sdm.rt.inventaris.inventaris-create';

        // Prefill dari overlay filter (optional)
        $this->holding_kode = $holdingKode ?: null;
        $this->lokasi_kode  = $lokasiKode  ?: null;
        $this->ruangan_kode = $ruanganKode ?: null;

        // Guard hirarki dasar
        if (!$this->holding_kode) {
            $this->lokasi_kode = null;
            $this->ruangan_kode = null;
            $this->recalculateNoUrut();
            return;
        }

        // Validasi lokasi harus milik holding
        if ($this->lokasi_kode) {
            $lokasiValid = \App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inv_Lokasi_List::query()
                ->where('holding_kode', $this->holding_kode)
                ->where('lokasi_kode', $this->lokasi_kode)
                ->exists();

            if (!$lokasiValid) {
                $this->lokasi_kode = null;
                $this->ruangan_kode = null;
                $this->recalculateNoUrut();
                return;
            }
        } else {
            $this->ruangan_kode = null;
            $this->recalculateNoUrut();
            return;
        }

        // Validasi ruangan harus milik holding + lokasi
        if ($this->ruangan_kode) {
            $ruanganValid = \App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inv_Ruangan_List::query()
                ->where('holding_kode', $this->holding_kode)
                ->where('lokasi_kode', $this->lokasi_kode)
                ->where('kode_ruangan', $this->ruangan_kode)
                ->exists();

            if (!$ruanganValid) {
                $this->ruangan_kode = null;
            }
        }

        $this->recalculateNoUrut();
    }


    /* =========================
     | OPTIONS (List = VIEW)
     ========================= */
    #[Computed]
    public function lokasiOptions(): array
    {
        if (!$this->holding_kode) return [];

        return Inv_Lokasi_List::where('holding_kode', $this->holding_kode)
            ->orderBy('lokasi_kode', 'asc')
            ->get()
            ->mapWithKeys(fn($item) => [$item->lokasi_kode => $item->label_lokasi]) // "02 - Lantai 2"
            ->toArray();
    }

    #[Computed]
    public function ruanganOptions(): array
    {
        if (!$this->holding_kode || !$this->lokasi_kode) return [];

        return Inv_Ruangan_List::where('holding_kode', $this->holding_kode)
            ->where('lokasi_kode', $this->lokasi_kode)
            ->orderBy('kode_ruangan', 'asc')
            ->get()
            ->mapWithKeys(fn($item) => [$item->kode_ruangan => $item->label_ruangan]) // "01 - SDM"
            ->toArray();
    }

    /* =========================
     | LABEL PREVIEW
     ========================= */
    #[Computed]
    public function generatedLabel(): string
    {
        if (!$this->holding_kode || !$this->lokasi_kode || !$this->ruangan_kode || !$this->jenis_barang_kode) {
            return 'AB.CDEFGH000.LMNO';
        }

        $ijk = str_pad(($this->no_urut_mulai > 0 ? $this->no_urut_mulai : 1), 3, '0', STR_PAD_LEFT);

        return "{$this->holding_kode}.{$this->lokasi_kode}{$this->ruangan_kode}{$this->jenis_barang_kode}{$ijk}.{$this->bulan}{$this->tahun}";
    }

    /* =========================
     | HIRARKI UPDATERS
     ========================= */
    public function updatedHoldingKode(): void
    {
        $this->lokasi_kode = null;
        $this->ruangan_kode = null;
        $this->recalculateNoUrut();
    }

    public function updatedLokasiKode(): void
    {
        $this->ruangan_kode = null;
        $this->recalculateNoUrut();
    }

    public function updatedRuanganKode(): void
    {
        $this->recalculateNoUrut();
    }


    public function updatedJenisBarangKode(): void
    {
        $this->recalculateNoUrut();
    }

    public function updatedQty($value): void
    {
        $value = is_numeric($value) ? (int) $value : 1;
        $this->qty = max(1, min(500, $value));
        $this->recalculateNoUrut();
    }

    /* =========================
     | NOMOR URUT (preview)
     ========================= */
    protected function recalculateNoUrut(): void
    {
        if ($this->holding_kode && $this->lokasi_kode && $this->ruangan_kode && $this->jenis_barang_kode) {
            $last = InventarisList::where('ab', $this->holding_kode)
                ->where('cd', $this->lokasi_kode)
                ->where('ef', $this->ruangan_kode)
                ->where('gh', $this->jenis_barang_kode)
                ->max('no_urut');

            $this->no_urut_terakhir = $last ? (int) $last : 0;
            $this->no_urut_mulai = $this->no_urut_terakhir + 1;
            return;
        }

        $this->no_urut_terakhir = 0;
        $this->no_urut_mulai = 0;
    }

    /* =========================
     | STORE (ERP-safe)
     ========================= */
    public function store(): void
    {
        $this->validate([
            // HOLDING: dari holdings.inv_code (AB)
            'holding_kode' => ['required', Rule::exists('holdings', 'inv_code')],

            'lokasi_kode' => [
                'required',
                Rule::exists('inv_lokasi', 'kode')->where(fn($q) =>
                    $q->where('holding_kode', $this->holding_kode)
                ),
            ],

            'ruangan_kode' => [
                'required',
                Rule::exists('inv_ruangan', 'kode')->where(fn($q) =>
                    $q->where('holding_kode', $this->holding_kode)
                      ->where('lokasi_kode', $this->lokasi_kode)
                ),
            ],

            'jenis_barang_kode' => ['required', Rule::exists('inv_jenis_barang', 'kode')],

            'nama_barang' => ['required', 'string', 'max:150'],
            'qty' => ['required', 'integer', 'min:1', 'max:500'],
            'bulan' => ['required', 'string', 'size:2'],
            'tahun' => ['required', 'string', 'size:2'],
        ], [
            'required' => ':attribute tidak boleh kosong',
        ]);

        $this->lastCreatedLabels = [];

        DB::transaction(function () {
            $lastRow = Inventaris::where('ab', $this->holding_kode)
                ->where('cd', $this->lokasi_kode)
                ->where('ef', $this->ruangan_kode)
                ->where('gh', $this->jenis_barang_kode)
                ->orderByDesc('no_urut')
                ->lockForUpdate()
                ->first();

            $lastNo = $lastRow?->no_urut ? (int) $lastRow->no_urut : 0;
            $startNoUrut = $lastNo + 1;

            for ($i = 0; $i < $this->qty; $i++) {
                $currentNoUrut = $startNoUrut + $i;
                $ijk = str_pad($currentNoUrut, 3, '0', STR_PAD_LEFT);

                $kodeLabel = "{$this->holding_kode}.{$this->lokasi_kode}{$this->ruangan_kode}{$this->jenis_barang_kode}{$ijk}.{$this->bulan}{$this->tahun}";

                Inventaris::create([
                    'kode_label'     => $kodeLabel,
                    'nama_barang'    => $this->nama_barang,
                    'description'    => $this->description,

                    'ab'             => $this->holding_kode,
                    'cd'             => $this->lokasi_kode,
                    'ef'             => $this->ruangan_kode,
                    'gh'             => $this->jenis_barang_kode,

                    'ijk'            => $ijk,
                    'no_urut'        => $currentNoUrut,

                    'Bulan'          => $this->bulan === '00' ? 0 : (int) $this->bulan,
                    'Tahun'          => $this->tahun === '00' ? 0 : (int) $this->tahun,

                    'status'         => 'Baik',
                    'tanggal_status' => now(),

                    'lifecycle_status' => 'active',
                    // created_by nanti isi dari auth
                ]);

                $this->lastCreatedLabels[] = $kodeLabel;
            }

            $this->no_urut_terakhir = $lastNo + $this->qty;
            $this->no_urut_mulai = $this->no_urut_terakhir + 1;
        });

        $this->showPrintConfirm = true;
    }

    /* =========================
     | CLOSE (overlay/page)
     ========================= */
    public function closeAndGoHome(): void
    {
        $this->dispatch('inventaris-overlay-close');
        $this->dispatch('inventaris-created'); // ✅ khusus create

        if ($this->standalone) {
            $this->redirect(route('holdings.hq.sdm.rt.inventaris.inventaris-table'), navigate: true);
        }
    }

    public function cancel(): void
    {
        $this->dispatch('inventaris-overlay-close');

        if ($this->standalone) {
            $this->redirect(route('holdings.hq.sdm.rt.inventaris.inventaris-table'), navigate: true);
        }
    }

    public function render()
    {
        // HOLDING dropdown dari holdings
        $data_holding = Holding::query()
            ->whereNotNull('inv_code')
            ->orderBy('inv_code')
            ->get()
            ->mapWithKeys(fn ($h) => [$h->inv_code => ($h->inv_code . ' - ' . $h->alias)])
            ->toArray();

        $data_jenis_barang = Inv_Jenis_Barang::pluck('jenis_barang', 'kode')->toArray();

        $view = view('livewire.holdings.hq.sdm.rt.inventaris.inventaris-create', [
            'data_holding' => $data_holding,
            'data_jenis_barang' => $data_jenis_barang,
        ]);

        return $this->standalone
            ? $view->layout('components.sccr-layout')
            : $view;
    }
}
