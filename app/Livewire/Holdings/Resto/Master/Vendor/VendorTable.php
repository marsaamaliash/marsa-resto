<?php

namespace App\Livewire\Holdings\Resto\Master\Vendor;

use App\Models\Holdings\Resto\Master\Rst_MasterVendor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VendorTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public bool $canApprove = false;

    public string $search = '';

    public string $filter1 = '';

    public string $filter2 = '';

    public string $filterStatus = '';

    public int $perPage = 10;

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public int $totalAll = 0;

    protected array $allowedSortFields = [
        'id',
        'name',
        'code',
        'email',
        'pic',
        'no_telp',
        'status',
        'is_active',
        'created_at',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

    public ?string $actionOverlayMode = null;

    public ?string $actionOverlayId = null;

    public string $actionNotes = '';

    public string $actionReason = '';

    public bool $showColumnPicker = false;

    public array $columnVisibility = [];

    public array $availableColumns = [
        ['key' => 'id', 'label' => 'ID', 'default' => true],
        ['key' => 'code', 'label' => 'Kode', 'default' => true],
        ['key' => 'name', 'label' => 'Nama', 'default' => true],
        ['key' => 'email', 'label' => 'Email', 'default' => true],
        ['key' => 'pic', 'label' => 'PIC', 'default' => true],
        ['key' => 'no_telp', 'label' => 'Telepon', 'default' => true],
        ['key' => 'default_terms', 'label' => 'Terms', 'default' => true],
        ['key' => 'status', 'label' => 'Status', 'default' => true],
        ['key' => 'is_active', 'label' => 'Aktif', 'default' => true],
        ['key' => 'created_at', 'label' => 'Dibuat', 'default' => false],
        ['key' => 'updated_at', 'label' => 'Diubah', 'default' => false],
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'filter2' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('MASTER_VENDOR_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('MASTER_VENDOR_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('MASTER_VENDOR_DELETE') ?? false);
        $this->canApprove = (bool) ($u?->hasPermission('MASTER_VENDOR_APPROVE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Master Data', 'route' => 'dashboard.resto.master', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Vendor', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();

        $this->totalAll = Rst_MasterVendor::withTrashed()->count();

        foreach ($this->availableColumns as $col) {
            $this->columnVisibility[$col['key']] = $col['default'];
        }
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    public function toggleColumnPicker(): void
    {
        $this->showColumnPicker = ! $this->showColumnPicker;
    }

    public function resetColumns(): void
    {
        foreach ($this->availableColumns as $col) {
            $this->columnVisibility[$col['key']] = $col['default'];
        }
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_MasterVendor::withTrashed();

        if ($this->filterStatus === 'requested') {
            $query->whereNull('deleted_at')->where('status', 'requested');
        } elseif ($this->filterStatus === 'approved') {
            $query->whereNull('deleted_at')->where('status', 'approved');
        } elseif ($this->filterStatus === 'rejected') {
            $query->whereNull('deleted_at')->where('status', 'rejected');
        } elseif ($this->filterStatus === 'deleted') {
            $query->onlyTrashed();
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('pic', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%");
            });
        }

        if ($this->filter1 !== '') {
            $query->where('default_terms', $this->filter1);
        }

        if ($this->filter2 !== '') {
            $query->where('is_active', $this->filter2);
        }

        if (in_array($this->sortField, $this->allowedSortFields, true)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->get();
    }

    protected function paginateCollection(Collection $collection, int $perPage): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    protected function visibleIds(): array
    {
        $p = $this->paginateCollection($this->dataQuery(), $this->perPage);

        return $p->getCollection()
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->toArray();
    }

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

    public function applyFilter(): void
    {
        $this->resetPage();
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filter1', 'filter2', 'filterStatus']);
        $this->applyFilter();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'perPage', 'sortField', 'sortDirection'], true)) {
            $this->resetPage();
        }
    }

    public function updatedSelectAll(bool $value): void
    {
        $visible = $this->visibleIds();

        if ($value) {
            $this->selectedItems = array_values(array_unique(array_merge($this->selectedItems, $visible)));

            return;
        }

        $this->selectedItems = array_values(array_diff($this->selectedItems, $visible));
    }

    public function updatedSelectedItems(): void
    {
        $visible = $this->visibleIds();
        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));
    }

    public function exportFiltered()
    {
        $data = $this->dataQuery();

        return $this->generateExcel($data, 'Filtered');
    }

    public function exportSelected()
    {
        if (empty($this->selectedItems)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Pilih data terlebih dahulu'];

            return null;
        }

        $ids = array_values(array_unique(array_map('strval', $this->selectedItems)));
        $data = Rst_MasterVendor::withTrashed()->whereIn('id', $ids)->get();

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $spreadsheet = new Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $headers = ['ID', 'Kode', 'Nama', 'Email', 'PIC', 'Telepon', 'Terms', 'Status', 'Aktif', 'Dibuat'];
        $ws->fromArray([$headers], null, 'A1');

        $termsLabels = [
            'cash' => 'Cash',
            '7_hari' => '7 Hari',
            '30_hari' => '30 Hari',
        ];

        $row = 2;
        foreach ($data as $item) {
            $status = $item->deleted_at ? 'Deleted' : ucfirst($item->status ?? 'requested');

            $ws->fromArray([
                $item->id,
                $item->code ?? '',
                $item->name,
                $item->email ?? '-',
                $item->pic ?? '-',
                $item->no_telp ?? '-',
                $termsLabels[$item->default_terms] ?? '-',
                $status,
                $item->is_active ? 'Ya' : 'Tidak',
                $item->created_at?->format('Y-m-d H:i:s') ?? '',
            ], null, 'A'.$row++);
        }

        foreach (range('A', 'J') as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Vendor_{$type}_".now()->format('Ymd_His').'.xlsx';

        $tmp = tempnam(sys_get_temp_dir(), 'vendor_');
        (new Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    public function openCreate(): void
    {
        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin create.'];

            return;
        }

        $this->selectedItems = [];
        $this->selectAll = false;

        $this->overlayMode = 'create';
        $this->overlayId = null;
    }

    public function openShow(string $id): void
    {
        $this->overlayMode = 'show';
        $this->overlayId = $id;
    }

    public function openEdit(string $id): void
    {
        if (! $this->canUpdate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin update.'];

            return;
        }

        $this->overlayMode = 'edit';
        $this->overlayId = $id;
    }

    public function openApprove(string $id): void
    {
        if (! $this->canApprove) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin approve.'];

            return;
        }

        $this->actionOverlayMode = 'approve';
        $this->actionOverlayId = $id;
        $this->actionReason = '';
    }

    public function openReject(string $id): void
    {
        if (! $this->canApprove) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin reject.'];

            return;
        }

        $this->actionOverlayMode = 'reject';
        $this->actionOverlayId = $id;
        $this->actionReason = '';
    }

    public function closeActionOverlay(): void
    {
        $this->reset(['actionOverlayMode', 'actionOverlayId', 'actionReason']);
    }

    public function approveVendor(): void
    {
        if (! $this->canApprove) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin approve.'];

            return;
        }

        $vendor = Rst_MasterVendor::withTrashed()->find($this->actionOverlayId);

        if (! $vendor) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($vendor->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya vendor dengan status Requested yang bisa di-approve.'];

            return;
        }

        $vendor->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Vendor berhasil di-approve.'];
        $this->closeActionOverlay();
    }

    public function rejectVendor(): void
    {
        if (! $this->canApprove) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin reject.'];

            return;
        }

        $this->validate([
            'actionReason' => ['required', 'string', 'max:65535'],
        ]);

        $vendor = Rst_MasterVendor::withTrashed()->find($this->actionOverlayId);

        if (! $vendor) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($vendor->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya vendor dengan status Requested yang bisa di-reject.'];

            return;
        }

        $vendor->update([
            'status' => 'rejected',
            'rejection_reason' => $this->actionReason,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Vendor berhasil di-reject.'];
        $this->closeActionOverlay();
    }

    public function deleteItem(string $id): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin delete.'];

            return;
        }

        $item = Rst_MasterVendor::withTrashed()->find($id);

        if (! $item) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        $item->delete();

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data berhasil dihapus.'];
    }

    public function restoreItem(string $id): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin restore.'];

            return;
        }

        $item = Rst_MasterVendor::onlyTrashed()->find($id);

        if (! $item) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        $item->restore();

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data berhasil di-restore.'];
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayId']);
    }

    #[On('vendor-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[On('vendor-created')]
    public function handleCreated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data berhasil ditambahkan.'];
    }

    #[On('vendor-updated')]
    public function handleUpdated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data berhasil diperbarui.'];
    }

    #[On('vendor-open-edit')]
    public function handleOpenEditFromShow(string $id): void
    {
        $this->openEdit($id);
    }

    protected function filter1Options(): array
    {
        return [
            '' => '-- Semua Terms --',
            'cash' => 'Cash',
            '7_hari' => '7 Hari',
            '30_hari' => '30 Hari',
        ];
    }

    protected function filter2Options(): array
    {
        return [
            '' => '-- Semua Aktif --',
            '1' => 'Aktif',
            '0' => 'Nonaktif',
        ];
    }

    public function render()
    {
        $data = $this->paginateCollection($this->dataQuery(), $this->perPage);

        $visible = $data->getCollection()
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));

        return view('livewire.holdings.resto.master.vendor.vendor-table', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
