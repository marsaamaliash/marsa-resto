<?php

namespace App\Livewire\Holdings\Hq\Sdm\Rt\Inventaris\Master\Lokasi;

use App\Models\Holding;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\Inv_Lokasi_List;
use App\Models\Holdings\Hq\Sdm\Rt\Inventaris\InvMasterLokasiDeleteRequest;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet; // VIEW v_inv_lokasi_list
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InvMasterLokasiTable extends Component
{
    use WithPagination;

    /* ===================== UI GLOBAL ===================== */
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    // Permission flags
    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    /* ===================== FILTER & SORT ===================== */
    public string $search = '';

    public string $filterHolding = '';

    public int $perPage = 10;

    public string $sortField = 'lokasi_kode';

    public string $sortDirection = 'asc';

    protected array $allowedSortFields = [
        'holding_kode',
        'nama_holding',
        'lokasi_kode',
        'nama_lokasi',
    ];

    /* ===================== SELECTION ===================== */
    public array $selected = []; // ["AB.CD", ...]

    public bool $selectAll = false;

    /* ===================== DELETE REQUEST ===================== */
    public bool $showConfirmModal = false;

    public ?string $confirmingKey = null;

    public bool $isBulkDelete = false;

    public string $deleteReason = '';

    /* ===================== OVERLAY ===================== */
    public ?string $overlayMode = null; // null|'create'|'edit'|'show'

    public ?string $overlayKey = null;  // "AB.CD"

    /* ===================== QUERY STRING ===================== */
    protected $queryString = [
        'search' => ['except' => ''],
        'filterHolding' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'lokasi_kode'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-200'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-200'],
            ['label' => 'SDM', 'route' => 'dashboard.sdm', 'color' => 'text-gray-200'],
            ['label' => 'Rumah Tangga', 'route' => 'dashboard.rt', 'color' => 'text-gray-200'],
            ['label' => 'Inventaris', 'route' => 'holdings.hq.sdm.rt.inventaris.inventaris-table', 'color' => 'text-gray-200'],
            ['label' => 'Master', 'route' => 'holdings.hq.sdm.rt.inventaris.master.index', 'color' => 'text-gray-200'],
            ['label' => 'Master Lokasi', 'color' => 'text-gray-200 font-semibold'],
        ];

        $user = auth()->user();

        $this->canCreate = (bool) ($user?->hasPermission('INV_MASTER_LOKASI_CREATE') ?? false);
        $this->canUpdate = (bool) ($user?->hasPermission('INV_MASTER_LOKASI_UPDATE') ?? false);
        $this->canDelete = (bool) ($user?->hasPermission('INV_MASTER_LOKASI_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    /* ===================== QUERY ===================== */
    protected function lokasiQuery()
    {
        $sortField = in_array($this->sortField, $this->allowedSortFields, true)
            ? $this->sortField
            : 'lokasi_kode';

        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Inv_Lokasi_List::query()
            ->when($this->search !== '', function ($q) {
                $s = $this->search;
                $q->where(function ($sub) use ($s) {
                    $sub->where('holding_kode', 'like', "%{$s}%")
                        ->orWhere('nama_holding', 'like', "%{$s}%")
                        ->orWhere('lokasi_kode', 'like', "%{$s}%")
                        ->orWhere('nama_lokasi', 'like', "%{$s}%");
                });
            })
            ->when($this->filterHolding !== '', fn ($q) => $q->where('holding_kode', $this->filterHolding))
            ->orderBy($sortField, $sortDirection);
    }

    /* ===================== SORT ===================== */
    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    /* ===================== FILTER ===================== */
    public function applyFilter(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterHolding']);
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
        $this->applyFilter();
    }

    /* ===================== SELECTION ===================== */
    public function updatedSelectAll(bool $value): void
    {
        $visible = $this->lokasiQuery()
            ->paginate($this->perPage)
            ->map(fn ($r) => (string) ($r->holding_kode.'.'.$r->lokasi_kode))
            ->toArray();

        if ($value) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $visible)));

            return;
        }

        $this->selected = array_values(array_diff($this->selected, $visible));
    }

    public function updatedSelected(): void
    {
        $visible = $this->lokasiQuery()
            ->paginate($this->perPage)
            ->map(fn ($r) => (string) ($r->holding_kode.'.'.$r->lokasi_kode))
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));
    }

    /* ===================== EXPORT ===================== */
    public function exportFiltered()
    {
        $data = $this->lokasiQuery()->get();

        return $this->generateExcel($data, 'Filtered');
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih data terlebih dahulu'];

            return null;
        }

        $pairs = array_map(function ($x) {
            $x = (string) $x;
            $parts = explode('.', $x, 2);

            return [($parts[0] ?? ''), ($parts[1] ?? '')];
        }, $this->selected);

        $q = Inv_Lokasi_List::query();
        $q->where(function ($w) use ($pairs) {
            foreach ($pairs as [$ab, $cd]) {
                $ab = strtoupper(trim((string) $ab));
                $cd = strtoupper(trim((string) $cd));
                if ($ab === '' || $cd === '') {
                    continue;
                }

                $w->orWhere(function ($x) use ($ab, $cd) {
                    $x->where('holding_kode', $ab)->where('lokasi_kode', $cd);
                });
            }
        });

        $data = $q->get();

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $sheet = new Spreadsheet;
        $ws = $sheet->getActiveSheet();

        $ws->fromArray([[
            'Holding Kode', 'Nama Holding', 'Lokasi Kode', 'Nama Lokasi',
        ]], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->holding_kode,
                $item->nama_holding,
                $item->lokasi_kode,
                $item->nama_lokasi,
            ], null, 'A'.$row++);
        }

        $filename = "INV_MasterLokasi_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new Xlsx($sheet);
        $tmp = tempnam(sys_get_temp_dir(), 'invml_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    /* ===================== OVERLAY CONTROL ===================== */
    public function openCreate(): void
    {
        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin create Master Lokasi.'];

            return;
        }

        $this->selected = [];
        $this->selectAll = false;

        $this->overlayMode = 'create';
        $this->overlayKey = null;
    }

    public function openShow(string $key): void
    {
        $this->overlayMode = 'show';
        $this->overlayKey = $key;
    }

    public function openEdit(string $key): void
    {
        if (! $this->canUpdate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin update Master Lokasi.'];

            return;
        }

        $this->overlayMode = 'edit';
        $this->overlayKey = $key;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayKey']);
    }

    /* ===================== DELETE REQUEST (ERP) ===================== */
    protected function createDeleteRequest(string $key, string $reason, int $userId): void
    {
        $key = strtoupper(trim($key));
        [$ab, $cd] = array_pad(explode('.', $key, 2), 2, '');

        $ab = strtoupper(trim((string) $ab));
        $cd = strtoupper(trim((string) $cd));

        if ($ab === '' || $cd === '') {
            throw new \RuntimeException("Key tidak valid: {$key}");
        }

        // pastikan data ada
        $exists = DB::table('inv_lokasi')
            ->where('holding_kode', $ab)
            ->where('kode', $cd)
            ->exists();

        if (! $exists) {
            throw new \RuntimeException("Lokasi {$ab}.{$cd} tidak ditemukan.");
        }

        $already = InvMasterLokasiDeleteRequest::query()
            ->where('holding_kode', $ab)
            ->where('lokasi_kode', $cd)
            ->where('status', 'pending')
            ->exists();

        if ($already) {
            throw new \RuntimeException("Request delete {$ab}.{$cd} sudah pending.");
        }

        InvMasterLokasiDeleteRequest::create([
            'holding_kode' => $ab,
            'lokasi_kode' => $cd,
            'reason' => $reason,
            'requested_by' => $userId,
            'requested_at' => now(),
            'status' => 'pending',
        ]);
    }

    public function openDeleteRequestSingle(string $key): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak punya izin delete.'];

            return;
        }

        $this->confirmingKey = $key;
        $this->isBulkDelete = false;
        $this->deleteReason = '';
        $this->showConfirmModal = true;
    }

    public function openDeleteRequestSelected(): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak punya izin delete.'];

            return;
        }

        if (empty($this->selected)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih data terlebih dahulu.'];

            return;
        }

        $this->isBulkDelete = true;
        $this->confirmingKey = null;
        $this->deleteReason = '';
        $this->showConfirmModal = true;
    }

    public function cancelDeleteRequest(): void
    {
        $this->reset(['showConfirmModal', 'confirmingKey', 'isBulkDelete', 'deleteReason']);
    }

    public function submitDeleteRequest(): void
    {
        if (! auth()->user()?->hasPermission('INV_MASTER_LOKASI_DELETE')) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak punya izin delete.'];

            return;
        }

        $reason = trim($this->deleteReason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Alasan wajib diisi (maks 255 karakter).'];

            return;
        }

        $action = app(\App\Actions\Inventaris\Master\RequestDeleteInvMasterLokasiAction::class);
        $userId = (int) auth()->id();

        $ok = 0;
        $fail = 0;
        $failMessages = [];

        // SINGLE
        if (! $this->isBulkDelete) {
            if (! $this->confirmingKey) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Key tidak valid.'];

                return;
            }

            try {
                [$ab, $cd] = explode('.', $this->confirmingKey, 2);
                $action->execute((string) $ab, (string) $cd, $reason, $userId);
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                $failMessages[] = $e->getMessage();
            }

            $this->finishAfterDeleteRequest($ok, $fail, $failMessages);

            return;
        }

        // BULK
        foreach ($this->selected as $key) {
            try {
                [$ab, $cd] = explode('.', (string) $key, 2);
                $action->execute((string) $ab, (string) $cd, $reason, $userId);
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                $failMessages[] = (string) $key.': '.$e->getMessage();
            }
        }

        $this->finishAfterDeleteRequest($ok, $fail, $failMessages);
    }

    protected function finishAfterDeleteRequest(int $ok, int $fail, array $failMessages): void
    {
        $this->cancelDeleteRequest();
        $this->selected = [];
        $this->selectAll = false;
        $this->resetPage();

        $msg = "Permintaan hapus dikirim: {$ok} item.";
        if ($fail > 0) {
            $msg .= " Gagal: {$fail} item. Contoh error: ".($failMessages[0] ?? 'unknown error');
        }

        $this->toast = [
            'show' => true,
            'type' => $fail === 0 ? 'success' : 'warning',
            'message' => $msg,
        ];
    }

    /* ===================== OPTIONS ===================== */
    protected function filterHoldingOptions(): array
    {
        return Holding::query()
            ->whereNotNull('inv_code')
            ->orderBy('inv_code')
            ->get()
            ->mapWithKeys(fn ($h) => [$h->inv_code => ($h->inv_code.' - '.$h->alias)])
            ->toArray();
    }

    #[\Livewire\Attributes\On('inv-master-lokasi-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[\Livewire\Attributes\On('inv-master-lokasi-created')]
    public function handleCreated(?string $rowKey = null): void
    {
        $this->closeOverlay();

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => 'Master Lokasi berhasil ditambahkan.',
        ];

        // opsional: kalau mau langsung open show
        // if ($rowKey) $this->openShow($rowKey);

        $this->resetPage();
    }

    #[\Livewire\Attributes\On('inv-master-lokasi-open-edit')]
    public function handleOpenEditFromShow(string $rowKey): void
    {
        $this->openEdit($rowKey);
    }

    #[\Livewire\Attributes\On('inv-master-lokasi-updated')]
    public function handleMasterLokasiUpdated(?string $rowKey = null): void
    {
        $this->closeOverlay();

        $this->toast = [
            'show' => true,
            'type' => 'success',
            'message' => 'Master Lokasi berhasil diperbarui.',
        ];

        // refresh table state
        $this->resetPage();

        // OPTIONAL: kalau mau setelah update langsung buka detailnya lagi
        // if ($rowKey) $this->openShow($rowKey);
    }

    public function render()
    {
        $rows = $this->lokasiQuery()->paginate($this->perPage);

        $visible = $rows
            ->map(fn ($r) => (string) ($r->holding_kode.'.'.$r->lokasi_kode))
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));

        return view('livewire.holdings.hq.sdm.rt.inventaris.master.lokasi.inv-master-lokasi-table', [
            'breadcrumbs' => $this->breadcrumbs,
            'holdingOptions' => $this->filterHoldingOptions(),
            'rows' => $rows,
        ])->layout('components.sccr-layout');
    }
}
