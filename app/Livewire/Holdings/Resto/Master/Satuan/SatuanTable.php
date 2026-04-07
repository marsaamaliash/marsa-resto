<?php

namespace App\Livewire\Holdings\Resto\Master\Satuan;

use App\Models\Holdings\Resto\Master\Rst_MasterSatuan;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;



class SatuanTable extends Component
{
    use WithPagination, WithFileUploads;

    /* =====================================================
     | UI GLOBAL STATE
     ===================================================== */
    public array $breadcrumbs = [];
    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    /**
     * Capability flags (BEST PRACTICE):
     * - dihitung sekali per request (mount + hydrate)
     * - Blade cuma baca boolean (tidak evaluasi permission berulang)
     */
    public bool $canWrite = false;
    public bool $canCreate = false;
    public bool $canUpdate = false;
    public bool $canDelete = false;

    public bool $canMasterMenu = false;
    public bool $canMasterLokasiCreate = false;
    public bool $canMasterLokasiView = false;
    public bool $canMasterRuanganCreate = false;
    public bool $canMasterJenisCreate = false;

    /* =====================================================
     | FILTER & SORT
     ===================================================== */
    public string $search = '';
    public string $filterHolding = '';
    public string $filterLokasi = '';
    public string $filterRuangan = '';

    public int $perPage = 10;
    public string $sortField = 'kode_label';
    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'kode_label',
        'nama_barang',
        'status',
        'tanggal_status',
        'nama_holding',
        'holding_alias',
        'nama_lokasi',
        'nama_ruangan',
        'nama_jenis',
        'no_urut',
        'Bulan',
        'Tahun',
    ];

    /* =====================================================
     | SELECTION
     ===================================================== */
    public array $selectedInventaris = [];
    public bool $selectAll = false;

    /* =====================================================
    | DELETE REQUEST (ERP)
    ===================================================== */
    public bool $showConfirmModal = false;
    public ?string $confirmingId = null;
    public bool $isBulkDelete = false;
    public string $deleteReason = '';

    /* =====================================================
     | OVERLAY (ERP SHOW/EDIT/CREATE)
     | overlayMode: null | 'show' | 'edit' | 'create'
     ===================================================== */
    public ?string $overlayMode = null; // null|'show'|'edit'|'create'
    public ?string $overlayKode = null;

    /* =====================================================
     | QUERY STRING
     ===================================================== */
    protected $queryString = [
        'search' => ['except' => ''],
        'filterHolding' => ['except' => ''],
        'filterLokasi' => ['except' => ''],
        'filterRuangan' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'kode_label'],
        'sortDirection' => ['except' => 'desc'],
    ];

    /* =====================================================
     | CAPABILITIES
     ===================================================== */
    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('INV_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('INV_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('INV_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;

        $this->canMasterLokasiCreate  = (bool) ($u?->hasPermission('INV_MASTER_LOKASI_CREATE') ?? false);
        $this->canMasterLokasiView    = (bool) ($u?->hasPermission('INV_MASTER_LOKASI_VIEW') ?? false);
        $this->canMasterRuanganCreate = (bool) ($u?->hasPermission('INV_MASTER_RUANGAN_CREATE') ?? false);
        $this->canMasterJenisCreate   = (bool) ($u?->hasPermission('INV_MASTER_JENIS_CREATE') ?? false);

        $this->canMasterMenu = $this->canMasterLokasiCreate
            || $this->canMasterLokasiView
            || $this->canMasterRuanganCreate
            || $this->canMasterJenisCreate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
             ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Master Data', 'route' => 'dashboard.resto.master','color' => 'text-gray-900 font-semibold'],
            ['label' => 'Satuan', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();
    }

    /**
     * Livewire akan re-render berkali-kali.
     * hydrate() memastikan capability selalu fresh per request
     * (dan Blade tidak menghitung permission berulang).
     */
    public function hydrate(): void
    {
        $this->syncCaps();
    }

    /* =====================================================
     | QUERY CORE
     ===================================================== */
    protected function inventarisQuery()
    {
        $sortField = in_array($this->sortField, $this->allowedSortFields, true)
            ? $this->sortField
            : 'kode_label';

        $sortDirection = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return Rst_MasterSatuan::query()
            ->when($this->search !== '', function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('kode_label', 'like', "%{$search}%")
                        ->orWhere('nama_barang', 'like', "%{$search}%")
                        ->orWhere('nama_lokasi', 'like', "%{$search}%")
                        ->orWhere('nama_ruangan', 'like', "%{$search}%")
                        ->orWhere('nama_holding', 'like', "%{$search}%")
                        ->orWhere('holding_alias', 'like', "%{$search}%")
                        ->orWhere('nama_jenis', 'like', "%{$search}%");
                });
            })
            ->when($this->filterHolding !== '', fn ($q) => $q->where('ab', $this->filterHolding))
            ->when($this->filterLokasi !== '', fn ($q) => $q->where('cd', $this->filterLokasi))
            ->when($this->filterRuangan !== '', fn ($q) => $q->where('ef', $this->filterRuangan))
            ->orderBy($sortField, $sortDirection);
    }

    protected function visibleKodeLabels(): array
    {
        $p = $this->inventarisQuery()->paginate($this->perPage);
        return $p->getCollection()
            ->pluck('kode_label')
            ->map(fn ($v) => (string) $v)
            ->toArray();
    }

    /* =====================================================
     | SORT
     ===================================================== */
    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSortFields, true)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    /* =====================================================
     | FILTER
     ===================================================== */
    public function applyFilter(): void
    {
        $this->resetPage();
        $this->selectedInventaris = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterHolding', 'filterLokasi', 'filterRuangan']);
        $this->applyFilter();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'perPage', 'sortField', 'sortDirection'], true)) {
            $this->resetPage();
        }
    }

    public function updatedFilterHolding(): void
    {
        $this->filterLokasi = '';
        $this->filterRuangan = '';
        $this->applyFilter();
    }

    public function updatedFilterLokasi(): void
    {
        $this->filterRuangan = '';
        $this->applyFilter();
    }

    /* =====================================================
     | SELECTION
     ===================================================== */
    public function updatedSelectAll(bool $value): void
    {
        $visible = $this->visibleKodeLabels();

        if ($value) {
            $this->selectedInventaris = array_values(array_unique(array_merge($this->selectedInventaris, $visible)));
            return;
        }

        $this->selectedInventaris = array_values(array_diff($this->selectedInventaris, $visible));
    }

    public function updatedSelectedInventaris(): void
    {
        $visible = $this->visibleKodeLabels();
        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedInventaris));
    }

    /* =====================================================
     | PRINT BULK
     ===================================================== */
    public function printBulk(): void
    {
        if (empty($this->selectedInventaris)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih item terlebih dahulu'];
            return;
        }

        $ids = implode(',', $this->selectedInventaris);
        $url = route('holdings.hq.sdm.rt.inventaris.inventaris-print-bulk') . '?ids=' . urlencode($ids);

        $this->dispatch('do-print-bulk', url: $url);
    }

    /* =====================================================
     | EXPORT
     ===================================================== */
    public function exportFiltered()
    {
        $data = $this->inventarisQuery()->get();
        return $this->generateExcel($data, 'Filtered');
    }

    public function exportSelected()
    {
        if (empty($this->selectedInventaris)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih data terlebih dahulu'];
            return null;
        }

        $ids = array_values(array_unique(array_map('strval', $this->selectedInventaris)));
        $data = Rst_MasterSatuan::whereIn('kode_label', $ids)->get();

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $sheet = new Spreadsheet();
        $ws = $sheet->getActiveSheet();

        $ws->fromArray([[
            'Kode Label', 'Nama Barang', 'Holding', 'Lokasi', 'Ruangan', 'Jenis', 'Status', 'Tanggal Status'
        ]], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->kode_label,
                $item->nama_barang,
                $item->nama_holding,
                $item->nama_lokasi,
                $item->nama_ruangan,
                $item->nama_jenis,
                $item->status,
                $item->tanggal_status,
            ], null, 'A' . $row++);
        }

        $filename = "Inventaris_{$type}_" . now()->format('Ymd_His') . ".xlsx";

        $writer = new Xlsx($sheet);
        $tmp = tempnam(sys_get_temp_dir(), 'inv_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    /* =====================================================
     | OVERLAY CONTROL (ERP) - WITH SERVER GUARD
     ===================================================== */
    public function openCreate(): void
    {
        if (!$this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin create Inventaris.'];
            return;
        }

        $this->selectedInventaris = [];
        $this->selectAll = false;

        $this->overlayMode = 'create';
        $this->overlayKode = null;
    }

    public function openShow(string $kodeLabel): void
    {
        $this->overlayMode = 'show';
        $this->overlayKode = $kodeLabel;
    }

    public function openEdit(string $kodeLabel): void
    {
        if (!$this->canUpdate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin update Inventaris.'];
            return;
        }

        $this->overlayMode = 'edit';
        $this->overlayKode = $kodeLabel;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayKode']);
    }

    #[On('inventaris-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[On('inventaris-created')]
    public function handleInventarisCreated(?string $kodeLabel = null): void
    {
        $this->closeOverlay();

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => 'Data inventaris berhasil ditambahkan.',
        ];
    }

    #[On('inventaris-updated')]
    public function handleInventarisUpdated(?string $kodeLabel = null): void
    {
        $this->closeOverlay();

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => 'Data inventaris berhasil diperbarui.',
        ];
    }

    #[On('inventaris-open-edit')]
    public function handleOpenEditFromShow(string $kodeLabel): void
    {
        $this->openEdit($kodeLabel);
    }

    /* =====================================================
     | DELETE REQUEST (ERP) - WITH SERVER GUARD
     ===================================================== */
    public function openDeleteRequestSingle(string $kodeLabel): void
    {
        if (!$this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak punya izin delete.'];
            return;
        }

        $this->confirmingId = $kodeLabel;
        $this->isBulkDelete = false;
        $this->deleteReason = '';
        $this->showConfirmModal = true;
    }

    public function openDeleteRequestSelected(): void
    {
        if (!$this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak punya izin delete.'];
            return;
        }

        if (empty($this->selectedInventaris)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih data terlebih dahulu.'];
            return;
        }

        $this->isBulkDelete = true;
        $this->confirmingId = null;
        $this->deleteReason = '';
        $this->showConfirmModal = true;
    }

    public function cancelDeleteRequest(): void
    {
        $this->reset(['showConfirmModal', 'confirmingId', 'isBulkDelete', 'deleteReason']);
    }

    public function submitDeleteRequest(): void
    {
        if (!$this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak punya izin delete.'];
            return;
        }

        $reason = trim($this->deleteReason);

        if ($reason === '' || mb_strlen($reason) > 255) {
            $this->toast = [
                'show' => true,
                'type' => 'warning',
                'message' => 'Alasan wajib diisi (maks 255 karakter).'
            ];
            return;
        }

        $action = app(RequestDeleteInventarisAction::class);
        $userId = (int) auth()->id();

        $ok = 0;
        $fail = 0;
        $failMessages = [];

        if (!$this->isBulkDelete) {
            if (!$this->confirmingId) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'ID tidak valid.'];
                return;
            }

            try {
                $action->execute($this->confirmingId, $reason, $userId);
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                $failMessages[] = $e->getMessage();
            }

            $this->finishAfterDeleteRequest($ok, $fail, $failMessages);
            return;
        }

        foreach ($this->selectedInventaris as $kodeLabel) {
            try {
                $action->execute((string) $kodeLabel, $reason, $userId);
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                $failMessages[] = (string) $kodeLabel . ': ' . $e->getMessage();
            }
        }

        $this->finishAfterDeleteRequest($ok, $fail, $failMessages);
    }

    protected function finishAfterDeleteRequest(int $ok, int $fail, array $failMessages): void
    {
        $this->cancelDeleteRequest();
        $this->selectedInventaris = [];
        $this->selectAll = false;
        $this->resetPage();

        $msg = "Permintaan hapus dikirim: {$ok} item.";
        if ($fail > 0) {
            $msg .= " Gagal: {$fail} item.";
            $msg .= " Contoh error: " . ($failMessages[0] ?? 'unknown error');
        }

        $this->toast = [
            'show' => true,
            'type' => $fail === 0 ? 'success' : 'warning',
            'message' => $msg,
        ];
    }

    /* =====================================================
     | FILTER OPTIONS (List = VIEW)
     ===================================================== */
    protected function filterHoldingOptions(): array
    {
        return Holding::query()
            ->whereNotNull('inv_code')
            ->orderBy('inv_code')
            ->get()
            ->mapWithKeys(fn ($h) => [$h->inv_code => ($h->inv_code . ' - ' . $h->alias)])
            ->toArray();
    }

    protected function filterLokasiOptions(): array
    {
        if ($this->filterHolding === '') return [];

        return Inv_Lokasi_List::where('holding_kode', $this->filterHolding)
            ->orderBy('lokasi_kode', 'asc')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->lokasi_kode => $item->label_lokasi])
            ->toArray();
    }

    protected function filterRuanganOptions(): array
    {
        if ($this->filterHolding === '' || $this->filterLokasi === '') return [];

        return Inv_Ruangan_List::where('holding_kode', $this->filterHolding)
            ->where('lokasi_kode', $this->filterLokasi)
            ->orderBy('kode_ruangan', 'asc')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->kode_ruangan => $item->label_ruangan])
            ->toArray();
    }

    public function openMasterModal(string $type): void
    {
        $this->dispatch('inv-master-open', type: $type);
    }

    #[On('inventaris-master-updated')]
    public function handleInventarisMasterUpdated(): void
    {
        // opsional: refresh filter dropdown
    }

    public function render()
    {
        $dataInventaris = $this->inventarisQuery()->paginate($this->perPage);

        $visible = $dataInventaris->getCollection()
            ->pluck('kode_label')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedInventaris));

        return view('livewire.holdings.resto.master.satuan.satuan-table', [
            'dataInventaris' => $dataInventaris,
            'breadcrumbs'    => $this->breadcrumbs,
            'holdingOptions' => $this->filterHoldingOptions(),
            'lokasiOptions'  => $this->filterLokasiOptions(),
            'ruanganOptions' => $this->filterRuanganOptions(),
        ])->layout('components.sccr-layout');
    }
}
