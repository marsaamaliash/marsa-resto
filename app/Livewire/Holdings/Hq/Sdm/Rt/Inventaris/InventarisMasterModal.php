<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris;

use App\Models\Holding;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inv_Jenis_List;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inv_Lokasi_List;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inv_Ruangan_List;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class InventarisMasterModal extends Component
{
    public bool $show = false;

    public string $type = ''; // lokasi | ruangan | jenis

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    // selectors
    public string $holdingKode = '';

    public string $lokasiKode = '';

    // create fields
    public string $kode = '';

    public string $nama = '';

    // list read-only (from VIEW)
    public $rows = [];

    #[On('inv-master-open')]
    public function open(string $type): void
    {
        abort_unless(in_array($type, ['lokasi', 'ruangan', 'jenis'], true), 422, 'Master type invalid');

        $this->authorizeType($type);

        $this->resetForm();
        $this->type = $type;
        $this->show = true;

        $this->loadList();
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetForm();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'holdingKode', 'lokasiKode'], true)) {
            $this->loadList();
        }
    }

    protected function authorizeType(string $type): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $permMap = [
            'lokasi' => 'INV_MASTER_LOKASI_CREATE',
            'ruangan' => 'INV_MASTER_RUANGAN_CREATE',
            'jenis' => 'INV_MASTER_JENIS_CREATE',
        ];

        abort_unless(isset($permMap[$type]), 422, 'Master type invalid');

        abort_unless(
            $user->hasPermission($permMap[$type]),
            403,
            'Tidak punya izin: '.$permMap[$type]
        );
    }

    protected function resetForm(): void
    {
        $this->reset([
            'toast',
            'search',
            'holdingKode',
            'lokasiKode',
            'kode',
            'nama',
            'rows',
        ]);

        $this->toast = ['show' => false, 'type' => 'success', 'message' => ''];
        $this->search = '';
    }

    protected function loadList(): void
    {
        $s = trim($this->search);

        if ($this->type === 'lokasi') {
            $q = Inv_Lokasi_List::query()
                ->when($this->holdingKode !== '', fn ($x) => $x->where('holding_kode', $this->holdingKode))
                ->when($s !== '', function ($x) use ($s) {
                    $x->where(function ($sub) use ($s) {
                        $sub->where('lokasi_kode', 'like', "%{$s}%")
                            ->orWhere('nama_lokasi', 'like', "%{$s}%")
                            ->orWhere('nama_holding', 'like', "%{$s}%");
                    });
                })
                ->orderBy('holding_kode')
                ->orderBy('lokasi_kode');

            $this->rows = $q->limit(200)->get();

            return;
        }

        if ($this->type === 'ruangan') {
            $q = Inv_Ruangan_List::query()
                ->when($this->holdingKode !== '', fn ($x) => $x->where('holding_kode', $this->holdingKode))
                ->when($this->lokasiKode !== '', fn ($x) => $x->where('lokasi_kode', $this->lokasiKode))
                ->when($s !== '', function ($x) use ($s) {
                    $x->where(function ($sub) use ($s) {
                        $sub->where('kode_ruangan', 'like', "%{$s}%")
                            ->orWhere('nama_ruangan', 'like', "%{$s}%")
                            ->orWhere('nama_lokasi', 'like', "%{$s}%");
                    });
                })
                ->orderBy('holding_kode')
                ->orderBy('lokasi_kode')
                ->orderBy('kode_ruangan');

            $this->rows = $q->limit(200)->get();

            return;
        }

        if ($this->type === 'jenis') {
            $q = Inv_Jenis_List::query()
                ->when($s !== '', function ($x) use ($s) {
                    $x->where(function ($sub) use ($s) {
                        $sub->where('jenis_kode', 'like', "%{$s}%")
                            ->orWhere('nama_jenis', 'like', "%{$s}%");
                    });
                })
                ->orderBy('jenis_kode');

            $this->rows = $q->limit(200)->get();

            return;
        }

        $this->rows = [];
    }

    public function save(): void
    {
        $this->authorizeType($this->type);

        $type = $this->type;
        $kode = strtoupper(trim($this->kode));
        $nama = trim($this->nama);

        if ($type === 'lokasi') {
            $this->validate([
                'holdingKode' => ['required', 'string', 'size:2'],
                'kode' => [
                    'required', 'string', 'max:2',
                    Rule::unique('inv_lokasi', 'kode')->where('holding_kode', $this->holdingKode),
                ],
                'nama' => ['required', 'string', 'max:150'],
            ], [
                'kode.unique' => 'Kode lokasi ini sudah terdaftar di Holding ini.',
            ]);

            DB::table('inv_lokasi')->insert([
                'holding_kode' => $this->holdingKode,
                'kode' => $kode,
                'lokasi' => $nama,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Master Lokasi berhasil ditambahkan.'];
            $this->reset(['kode', 'nama']);
            $this->loadList();
            $this->dispatch('inventaris-master-updated');

            return;
        }

        if ($type === 'ruangan') {
            $this->validate([
                'holdingKode' => ['required', 'string', 'size:2'],
                'lokasiKode' => ['required', 'string', 'size:2'],
                'kode' => [
                    'required', 'string', 'max:2',
                    Rule::unique('inv_ruangan', 'kode')
                        ->where('holding_kode', $this->holdingKode)
                        ->where('lokasi_kode', $this->lokasiKode),
                ],
                'nama' => ['required', 'string', 'max:100'],
            ]);

            DB::table('inv_ruangan')->insert([
                'holding_kode' => $this->holdingKode,
                'lokasi_kode' => $this->lokasiKode,
                'kode' => $kode,
                'nama_ruang' => $nama,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Master Ruangan berhasil ditambahkan.'];
            $this->reset(['kode', 'nama']);
            $this->loadList();
            $this->dispatch('inventaris-master-updated');

            return;
        }

        if ($type === 'jenis') {
            $this->validate([
                'kode' => ['required', 'string', 'max:2', Rule::unique('inv_jenis_barang', 'kode')],
                'nama' => ['required', 'string', 'max:150'],
            ]);

            DB::table('inv_jenis_barang')->insert([
                'kode' => $kode,
                'jenis_barang' => $nama,
            ]);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Master Jenis berhasil ditambahkan.'];
            $this->reset(['kode', 'nama']);
            $this->loadList();
            $this->dispatch('inventaris-master-updated');

            return;
        }

        abort(422, 'Master type invalid');
    }

    public function getHoldingOptionsProperty(): array
    {
        return Holding::query()
            ->whereNotNull('inv_code')
            ->orderBy('inv_code')
            ->get()
            ->mapWithKeys(fn ($h) => [$h->inv_code => ($h->inv_code.' - '.$h->alias)])
            ->toArray();
    }

    public function getLokasiOptionsProperty(): array
    {
        if ($this->holdingKode === '') {
            return [];
        }

        return Inv_Lokasi_List::where('holding_kode', $this->holdingKode)
            ->orderBy('lokasi_kode')
            ->get()
            ->mapWithKeys(fn ($x) => [$x->lokasi_kode => $x->label_lokasi])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.holdings.hq.sdm.rt.inventaris.inventaris-master-modal');
    }
}
