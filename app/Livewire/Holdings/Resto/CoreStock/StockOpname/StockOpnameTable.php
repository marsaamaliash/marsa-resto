<?php

namespace App\Livewire\Holdings\Resto\CoreStock\StockOpname;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockOpname;
use App\Models\Holdings\Resto\CoreStock\Rst_StockOpnameFreeze;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Services\Resto\StockOpnameService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StockOpnameTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    public string $filter1 = '';

    public string $filterStatus = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'id',
        'reference_number',
        'location_id',
        'opname_date',
        'status',
        'checker_name',
        'created_at',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

    public int $createLocationId = 0;

    public string $createCheckerName = '';

    public string $createCheckerRole = '';

    public string $createWitnessName = '';

    public string $createWitnessRole = '';

    public string $createOpnameDate = '';

    public string $createRemark = '';

    public array $createItems = [];

    public bool $showColumnPicker = false;

    public array $columnVisibility = [];

    public array $availableColumns = [
        ['key' => 'id', 'label' => 'ID', 'default' => true],
        ['key' => 'reference_number', 'label' => 'Reference', 'default' => true],
        ['key' => 'location_id', 'label' => 'Lokasi', 'default' => true],
        ['key' => 'opname_date', 'label' => 'Tanggal', 'default' => true],
        ['key' => 'status', 'label' => 'Status', 'default' => true],
        ['key' => 'checker_name', 'label' => 'Checker', 'default' => true],
        ['key' => 'witness_name', 'label' => 'Witness', 'default' => false],
        ['key' => 'is_frozen', 'label' => 'Frozen', 'default' => false],
        ['key' => 'remark', 'label' => 'Remark', 'default' => false],
        ['key' => 'created_at', 'label' => 'Created', 'default' => false],
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Core Stock', 'route' => 'dashboard.resto.core-stock', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Stock Opname', 'color' => 'text-gray-900 font-semibold'],
        ];

        foreach ($this->availableColumns as $col) {
            $this->columnVisibility[$col['key']] = $col['default'];
        }
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_StockOpname::withTrashed()->with(['location', 'items.item', 'items.uom']);

        if ($this->filterStatus === 'deleted') {
            $query->onlyTrashed();
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('location', fn ($lq) => $lq->where('name', 'like', "%{$search}%"))
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('checker_name', 'like', "%{$search}%")
                    ->orWhere('witness_name', 'like', "%{$search}%");
            });
        }

        if ($this->filter1 !== '') {
            $query->where('status', $this->filter1);
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
        $this->reset(['search', 'filter1', 'filterStatus']);
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

    public function freezeAll(): void
    {
        try {
            $locations = Rst_MasterLokasi::where('is_active', true)->get();

            foreach ($locations as $loc) {
                $existing = Rst_StockOpnameFreeze::where('location_id', $loc->id)
                    ->where('status', 'frozen')
                    ->first();

                if (! $existing) {
                    Rst_StockOpnameFreeze::create([
                        'location_id' => $loc->id,
                        'reference_number' => 'SO-FREEZE',
                        'frozen_by' => auth()->user()?->name ?? 'SYSTEM',
                        'frozen_at' => now(),
                        'status' => 'frozen',
                    ]);
                }
            }

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Semua lokasi di-freeze. Silakan buat Stock Opname.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function unfreezeAll(): void
    {
        try {
            Rst_StockOpnameFreeze::where('status', 'frozen')
                ->update(['status' => 'released']);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Semua lokasi di-unfreeze.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function openCreateOverlay(): void
    {
        $this->overlayMode = 'create';
        $this->overlayId = null;
        $this->createLocationId = 0;
        $this->createCheckerName = '';
        $this->createCheckerRole = '';
        $this->createWitnessName = '';
        $this->createWitnessRole = '';
        $this->createOpnameDate = now()->format('Y-m-d');
        $this->createRemark = '';
        $this->createItems = [];
    }

    public function updatedCreateLocationId(): void
    {
        if ($this->createLocationId > 0) {
            $itemIds = Rst_StockBalance::where('location_id', $this->createLocationId)
                ->pluck('item_id')
                ->toArray();

            $items = Rst_MasterItem::where('is_active', true)
                ->whereIn('id', $itemIds)
                ->orderBy('name')
                ->get();

            $this->createItems = $items->map(fn ($item) => [
                'item_id' => $item->id,
                'physical_qty' => 0,
                'remark' => '',
            ])->toArray();
        } else {
            $this->createItems = [];
        }
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayId', 'createLocationId', 'createCheckerName', 'createCheckerRole', 'createWitnessName', 'createWitnessRole', 'createOpnameDate', 'createRemark', 'createItems']);
    }

    public function addCreateItemRow(): void
    {
        $this->createItems[] = ['item_id' => 0, 'physical_qty' => 0, 'remark' => ''];
    }

    public function removeCreateItemRow(int $index): void
    {
        if (count($this->createItems) > 1) {
            unset($this->createItems[$index]);
            $this->createItems = array_values($this->createItems);
        }
    }

    public function getLocations(): array
    {
        return Rst_MasterLokasi::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($loc) => ['id' => $loc->id, 'name' => $loc->name])
            ->toArray();
    }

    public function getAvailableItems(): array
    {
        if ($this->createLocationId === 0) {
            return [];
        }

        $itemIds = Rst_StockBalance::where('location_id', $this->createLocationId)
            ->pluck('item_id')
            ->toArray();

        if (empty($itemIds)) {
            return [];
        }

        return Rst_MasterItem::where('is_active', true)
            ->whereIn('id', $itemIds)
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'uom_symbols' => $item->uom?->symbols ?? '-',
            ])
            ->toArray();
    }

    public function getSystemQty(int $itemId): float
    {
        if ($this->createLocationId === 0) {
            return 0;
        }

        return Rst_StockBalance::where('item_id', $itemId)
            ->where('location_id', $this->createLocationId)
            ->value('qty_available') ?? 0;
    }

    public function processCreate(): void
    {
        if ($this->createLocationId === 0) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Pilih lokasi.'];

            return;
        }

        if ($this->createCheckerName === '') {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Nama checker wajib diisi.'];

            return;
        }

        if ($this->createWitnessName === '') {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Nama witness wajib diisi.'];

            return;
        }

        if ($this->createOpnameDate === '') {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Tanggal opname wajib diisi.'];

            return;
        }

        $itemsToSave = [];
        foreach ($this->createItems as $item) {
            if ($item['item_id'] > 0) {
                $itemsToSave[] = [
                    'item_id' => (int) $item['item_id'],
                    'physical_qty' => (float) $item['physical_qty'],
                    'remark' => $item['remark'] ?? null,
                ];
            }
        }

        if (empty($itemsToSave)) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Pilih minimal 1 item.'];

            return;
        }

        try {
            StockOpnameService::createOpname(
                $this->createLocationId,
                $this->createCheckerName,
                $this->createCheckerRole,
                $this->createWitnessName,
                $this->createWitnessRole,
                $this->createOpnameDate,
                $itemsToSave,
                $this->createRemark ?: null
            );

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock Opname berhasil dibuat.'];
            $this->closeOverlay();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteItem(string $id): void
    {
        $item = Rst_StockOpname::withTrashed()->find($id);

        if (! $item) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        $item->delete();

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock Opname berhasil dihapus.'];
    }

    public function restoreItem(string $id): void
    {
        $item = Rst_StockOpname::onlyTrashed()->find($id);

        if (! $item) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        $item->restore();

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock Opname berhasil di-restore.'];
    }

    public function cloneItem(string $id): void
    {
        try {
            StockOpnameService::cloneOpname((int) $id);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock Opname berhasil di-clone.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitOpname(string $id): void
    {
        try {
            StockOpnameService::submitOpname((int) $id);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock Opname berhasil disubmit. Lokasi di-freeze.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function excChefCanApprove(string $id): void
    {
        $opname = Rst_StockOpname::find($id);
        if (! $opname) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($opname->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($opname->approval_level ?? 0) !== 0) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Exc Chef sudah approve.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'Exc Chef';
            StockOpnameService::approveOpname((int) $id, 1, $approverName, 'Approved by Exc Chef');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by Exc Chef.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rmCanApprove(string $id): void
    {
        $opname = Rst_StockOpname::find($id);
        if (! $opname) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($opname->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($opname->approval_level ?? 0) !== 1) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Belum di-approve oleh Exc Chef.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'RM';
            StockOpnameService::approveOpname((int) $id, 2, $approverName, 'Approved by RM');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function spvCanApprove(string $id): void
    {
        $opname = Rst_StockOpname::find($id);
        if (! $opname) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($opname->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($opname->approval_level ?? 0) !== 2) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Belum di-approve oleh RM.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'Supervisor';
            StockOpnameService::approveOpname((int) $id, 3, $approverName, 'Approved by SPV');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by SPV.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function finalizeOpname(string $id): void
    {
        try {
            StockOpnameService::finalizeOpname((int) $id);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Stock adjustment berhasil dilakukan. Lokasi di-unfreeze.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectOpname(string $id): void
    {
        try {
            $opname = Rst_StockOpname::find($id);
            if (! $opname) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

                return;
            }

            StockOpnameService::rejectOpname((int) $id, auth()->user()?->name ?? 'SYSTEM', 'Rejected');
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Stock Opname ditolak. Lokasi di-unfreeze.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function cancelOpname(string $id): void
    {
        try {
            StockOpnameService::cancelOpname((int) $id);

            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Stock Opname dibatalkan. Lokasi di-unfreeze.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
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
        $data = Rst_StockOpname::withTrashed()->whereIn('id', $ids)->get();

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel(Collection $data, string $type)
    {
        $spreadsheet = new Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $headers = ['ID', 'Reference No', 'Lokasi', 'Tanggal', 'Status', 'Checker', 'Witness', 'Frozen', 'Created'];
        $ws->fromArray([$headers], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->id,
                $item->reference_number ?? '',
                $item->location?->name ?? '-',
                $item->opname_date?->format('Y-m-d') ?? '-',
                $item->status,
                $item->checker_name ?? '-',
                $item->witness_name ?? '-',
                $item->is_frozen ? 'Ya' : 'Tidak',
                $item->created_at?->format('Y-m-d H:i:s') ?? '',
            ], null, 'A'.$row++);
        }

        foreach (range('A', 'I') as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "StockOpname_{$type}_".now()->format('Ymd_His').'.xlsx';

        $tmp = tempnam(sys_get_temp_dir(), 'stockopname_');
        (new Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    protected function filter1Options(): array
    {
        return [
            '' => '-- Semua Status --',
            'draft' => 'Draft',
            'requested' => 'Requested',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
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

        return view('livewire.holdings.resto.core-stock.stock-opname.stock-opname-table', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
        ])->layout('components.sccr-layout');
    }
}
