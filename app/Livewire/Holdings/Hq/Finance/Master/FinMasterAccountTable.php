<?php

namespace App\Livewire\Holdings\Hq\Finance\Master;

use App\Models\Holdings\Hq\Finance\Fin_Account_List;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; // VIEW: v_fin_accounts

class FinMasterAccountTable extends Component
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

    public string $filterHolding = ''; // holding_id

    public int $perPage = 10;

    public string $sortField = 'code';

    public string $sortDirection = 'asc';

    protected array $allowedSortFields = [
        'holding_name',
        'department_name',
        'division_name',
        'code',
        'name',
        'type',
        'status',
        'requested_at',
        'is_active',
        'id',
    ];

    /* ===================== SELECTION ===================== */
    public array $selected = []; // ["1","2","3", ...] (id sebagai string)

    public bool $selectAll = false;

    /* ===================== DELETE REQUEST ===================== */
    public bool $showConfirmModal = false;

    public ?string $confirmingKey = null; // id (string)

    public bool $isBulkDelete = false;

    public string $deleteReason = '';

    /* ===================== OVERLAY ===================== */
    public ?string $overlayMode = null; // null|'create'|'edit'|'show'

    public ?string $overlayKey = null;  // id (string)

    /* ===================== QUERY STRING ===================== */
    protected $queryString = [
        'search' => ['except' => ''],
        'filterHolding' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'code'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-200'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-200'],
            ['label' => 'Finance', 'route' => 'dashboard.finance', 'color' => 'text-gray-200'],
            ['label' => 'Master Account', 'color' => 'text-gray-200 font-semibold'],
        ];

        $user = auth()->user();

        $this->canCreate = (bool) ($user?->hasPermission('FIN_MASTER_ACCOUNT_CREATE') ?? false);
        $this->canUpdate = (bool) ($user?->hasPermission('FIN_MASTER_ACCOUNT_UPDATE') ?? false);
        $this->canDelete = (bool) ($user?->hasPermission('FIN_MASTER_ACCOUNT_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    /* ===================== QUERY ===================== */
    protected function tableListQuery()
    {
        // backward-compat untuk query string lama hasil clone
        $requested = (string) $this->sortField;
        if ($requested === 'Account_kode') {
            $requested = 'code';
        }
        if ($requested === 'nama_Account') {
            $requested = 'name';
        }
        if ($requested === 'holding_kode') {
            $requested = 'holding_name';
        }
        if ($requested === 'nama_holding') {
            $requested = 'holding_name';
        }

        $sortField = in_array($requested, $this->allowedSortFields, true) ? $requested : 'code';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return Fin_Account_List::query()
            ->when(trim($this->search) !== '', function ($q) {
                $s = trim($this->search);

                $q->where(function ($sub) use ($s) {
                    $sub->where('code', 'like', "%{$s}%")
                        ->orWhere('name', 'like', "%{$s}%")
                        ->orWhere('type', 'like', "%{$s}%")
                        ->orWhere('status', 'like', "%{$s}%")
                        ->orWhere('holding_name', 'like', "%{$s}%")
                        ->orWhere('department_name', 'like', "%{$s}%")
                        ->orWhere('division_name', 'like', "%{$s}%");
                });
            })
            ->when($this->filterHolding !== '', function ($q) {
                $q->where('holding_id', (int) $this->filterHolding);
            })
            ->orderBy($sortField, $sortDirection)
            ->orderBy('id', 'desc'); // stabil
    }

    protected function visibleIds(): array
    {
        $p = $this->tableListQuery()->paginate($this->perPage);

        return $p->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    /* ===================== SORT ===================== */
    public function sortBy(string $field): void
    {
        if (! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            $this->resetPage();

            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
        $this->resetPage();
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
        if (in_array($property, ['search', 'perPage', 'sortField', 'sortDirection', 'filterHolding'], true)) {
            $this->resetPage();
            // biar selection nggak nyangkut saat filter berubah
            $this->selected = [];
            $this->selectAll = false;
        }
    }

    /* ===================== SELECTION ===================== */
    public function updatedSelectAll(bool $value): void
    {
        $visible = $this->visibleIds();

        if ($value) {
            $this->selected = array_values(array_unique(array_merge($this->selected, $visible)));

            return;
        }

        $this->selected = array_values(array_diff($this->selected, $visible));
    }

    public function updatedSelected(): void
    {
        $visible = $this->visibleIds();
        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));
    }

    /* ===================== EXPORT ===================== */
    public function exportFiltered()
    {
        $data = $this->tableListQuery()->get();

        return $this->generateExcel($data, 'Filtered');
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih data terlebih dahulu'];

            return null;
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $this->selected), fn ($v) => $v > 0)));
        if (empty($ids)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'ID terpilih tidak valid'];

            return null;
        }

        $data = $this->tableListQuery()->whereIn('id', $ids)->get();

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $sheet = new Spreadsheet;
        $ws = $sheet->getActiveSheet();

        $ws->fromArray([[
            'ID',
            'Holding',
            'Department',
            'Division',
            'CoA Code',
            'Nama Akun',
            'Type',
            'Active',
            'Status',
            'Requested At',
        ]], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $requestedAt = $item->requested_at
                ? Carbon::parse($item->requested_at)->format('Y-m-d H:i:s')
                : '';

            $ws->fromArray([
                (int) $item->id,
                (string) ($item->holding_name ?? ''),
                (string) ($item->department_name ?? ''),
                (string) ($item->division_name ?? ''),
                (string) ($item->code ?? ''),
                (string) ($item->name ?? ''),
                (string) ($item->type ?? ''),
                ((int) ($item->is_active ?? 0) === 1) ? 'ACTIVE' : 'INACTIVE',
                (string) ($item->status ?? ''),
                $requestedAt,
            ], null, 'A'.$row++);
        }

        $filename = "Fin_MasterAccount_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new Xlsx($sheet);
        $tmp = tempnam(sys_get_temp_dir(), 'Finml_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    /* ===================== OVERLAY CONTROL ===================== */
    public function openCreate(): void
    {
        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin create Master Account.'];

            return;
        }

        $this->selected = [];
        $this->selectAll = false;

        $this->overlayMode = 'create';
        $this->overlayKey = null;
    }

    public function openShow(string $id): void
    {
        $this->overlayMode = 'show';
        $this->overlayKey = $id;
    }

    public function openEdit(string $id): void
    {
        if (! $this->canUpdate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin update Master Account.'];

            return;
        }

        $this->overlayMode = 'edit';
        $this->overlayKey = $id;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayKey']);
    }

    /* ===================== DELETE REQUEST (STATUS + REQUESTED_AT) ===================== */
    protected function buildDeleteUpdatePayload(string $reason, int $userId): array
    {
        $payload = [
            'status' => 'pending_delete',
            'requested_at' => now(),
        ];

        // isi kalau kolomnya ada
        if (Schema::hasColumn('fin_accounts', 'requested_by')) {
            $payload['requested_by'] = $userId;
        }
        if (Schema::hasColumn('fin_accounts', 'requested_reason')) {
            $payload['requested_reason'] = $reason;
        } elseif (Schema::hasColumn('fin_accounts', 'delete_reason')) {
            $payload['delete_reason'] = $reason;
        } elseif (Schema::hasColumn('fin_accounts', 'reason')) {
            $payload['reason'] = $reason;
        }

        return $payload;
    }

    protected function requestDeleteByIds(array $ids, string $reason, int $userId): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($v) => $v > 0)));
        if (empty($ids)) {
            return [0, 1, ['ID tidak valid']];
        }

        $payload = $this->buildDeleteUpdatePayload($reason, $userId);

        $ok = 0;
        $fail = 0;
        $failMessages = [];

        DB::beginTransaction();
        try {
            $rows = DB::table('fin_accounts')
                ->whereIn('id', $ids)
                ->select(['id', 'status'])
                ->lockForUpdate()
                ->get();

            $foundIds = $rows->pluck('id')->all();
            $missing = array_values(array_diff($ids, $foundIds));
            foreach ($missing as $mid) {
                $fail++;
                $failMessages[] = "ID {$mid} tidak ditemukan.";
            }

            foreach ($rows as $r) {
                $st = (string) ($r->status ?? '');

                // kalau sudah pending delete, jangan dobel request
                if ($st === 'pending_delete' || $st === 'pending') {
                    $fail++;
                    $failMessages[] = "ID {$r->id} sudah pending.";

                    continue;
                }

                DB::table('fin_accounts')->where('id', (int) $r->id)->update($payload);
                $ok++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return [0, count($ids), [$e->getMessage()]];
        }

        return [$ok, $fail, $failMessages];
    }

    public function openDeleteRequestSingle(string $id): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak punya izin delete.'];

            return;
        }

        $this->confirmingKey = $id;
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
        if (! auth()->user()?->hasPermission('FIN_MASTER_ACCOUNT_DELETE')) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Anda tidak punya izin delete.'];

            return;
        }

        $reason = trim($this->deleteReason);
        if ($reason === '' || mb_strlen($reason) > 255) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Alasan wajib diisi (maks 255 karakter).'];

            return;
        }

        $userId = (int) auth()->id();

        // SINGLE
        if (! $this->isBulkDelete) {
            if (! $this->confirmingKey) {
                $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'ID tidak valid.'];

                return;
            }

            [$ok, $fail, $failMessages] = $this->requestDeleteByIds([(int) $this->confirmingKey], $reason, $userId);
            $this->finishAfterDeleteRequest($ok, $fail, $failMessages);

            return;
        }

        // BULK
        [$ok, $fail, $failMessages] = $this->requestDeleteByIds($this->selected, $reason, $userId);
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
        return \App\Models\Holding::query()
            ->orderBy('name')
            ->get(['id', 'name', 'alias'])
            ->mapWithKeys(fn ($h) => [(string) $h->id => ($h->name.($h->alias ? ' - '.$h->alias : ''))])
            ->toArray();
    }

    #[\Livewire\Attributes\On('fin-master-account-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[\Livewire\Attributes\On('fin-master-account-created')]
    public function handleCreated(?string $rowKey = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Master Account berhasil ditambahkan.'];
        $this->resetPage();
    }

    #[\Livewire\Attributes\On('fin-master-account-open-edit')]
    public function handleOpenEditFromShow(string $rowKey): void
    {
        $this->openEdit($rowKey);
    }

    #[\Livewire\Attributes\On('fin-master-account-updated')]
    public function handleMasterAccountUpdated(?string $rowKey = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Master Account berhasil diperbarui.'];
        $this->resetPage();
    }

    public function render()
    {
        $rows = $this->tableListQuery()->paginate($this->perPage);

        // selectAll untuk current page
        $visible = $rows->getCollection()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selected));

        return view('livewire.holdings.hq.finance.master.fin-master-account-table', [
            'breadcrumbs' => $this->breadcrumbs,
            'holdingOptions' => $this->filterHoldingOptions(),
            'rows' => $rows,
        ])->layout('components.sccr-layout');
    }
}
