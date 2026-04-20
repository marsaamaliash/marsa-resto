<?php

namespace App\Livewire\Holdings\Resto\Resep\KonversiSatuan;

use App\Models\Holdings\Resto\Resep\Rst_KonversiSatuan;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KonversiSatuanTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

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
        'created_at',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

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

        $this->canCreate = (bool) ($u?->hasPermission('MASTER_SATUAN_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('MASTER_SATUAN_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('MASTER_SATUAN_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Konversi Satuan', 'route' => 'dashboard.resto.resep', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Konversi Satuan', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();

        $this->totalAll = Rst_KonversiSatuan::withTrashed()->count();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_KonversiSatuan::withTrashed()
            ->with(['item', 'fromUom', 'toUom']);

        if ($this->filterStatus === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($this->filterStatus === 'draft') {
            $query->whereNull('deleted_at');
        } elseif ($this->filterStatus === 'deleted') {
            $query->onlyTrashed();
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
                ->orWhereHas('fromUom', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('toUom', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        if ($this->filter1 !== '') {
            $query->whereHas('item', function ($q) {
                $q->where('type', $this->filter1);
            });
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
        $data = Rst_KonversiSatuan::withTrashed()
            ->with(['item', 'fromUom', 'toUom'])
            ->whereIn('id', $ids)
            ->get();

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $spreadsheet = new Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $headers = ['ID', 'Item', 'Dari Satuan', 'Ke Satuan', 'Nilai Konversi', 'Status', 'Dibuat'];
        $ws->fromArray([$headers], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $status = $item->deleted_at ? 'Deleted' : 'Active';

            $ws->fromArray([
                $item->id,
                $item->item?->name ?? '-',
                $item->fromUom?->name ?? '-',
                $item->toUom?->name ?? '-',
                $item->conversion_factor,
                $status,
                $item->created_at?->format('Y-m-d H:i:s') ?? '',
            ], null, 'A'.$row++);
        }

        foreach (range('A', 'G') as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "KonversiSatuan_{$type}_".now()->format('Ymd_His').'.xlsx';

        $tmp = tempnam(sys_get_temp_dir(), 'konversi_satuan_');
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

    public function deleteItem(string $id): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin delete.'];

            return;
        }

        $item = Rst_KonversiSatuan::withTrashed()->find($id);

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

        $item = Rst_KonversiSatuan::onlyTrashed()->find($id);

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

    #[On('konversi-satuan-created')]
    public function handleCreated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Konversi Satuan berhasil ditambahkan.'];
    }

    #[On('konversi-satuan-updated')]
    public function handleUpdated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Konversi Satuan berhasil diperbarui.'];
    }

    #[On('konversi-satuan-open-edit')]
    public function handleOpenEditFromShow(string $id): void
    {
        $this->openEdit($id);
    }

    #[On('konversi-satuan-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    protected function filter1Options(): array
    {
        return [
            '' => '-- Semua Tipe --',
            'weight' => 'Weight',
            'volume' => 'Volume',
            'unit' => 'Unit',
        ];
    }

    protected function filter2Options(): array
    {
        return [];
    }

    public function render()
    {
        $data = $this->paginateCollection($this->dataQuery(), $this->perPage);

        $visible = $data->getCollection()
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));

        return view('livewire.holdings.resto.resep.konversi-satuan.konversi-satuan-table', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
