<?php

namespace App\Livewire\Holdings\Resto\Master\Lokasi;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class LokasiTable extends Component
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

    public int $perPage = 10;

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'id',
        'name',
        'code',
        'type',
        'pic_name',
        'is_active',
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
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('MASTER_LOKASI_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('MASTER_LOKASI_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('MASTER_LOKASI_DELETE') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Master Data', 'route' => 'dashboard.resto.master', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Lokasi', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_MasterLokasi::query();

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        if ($this->filter1 !== '') {
            $query->where('type', $this->filter1);
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
        $this->reset(['search', 'filter1', 'filter2']);
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
        $data = $this->dataQuery()->whereIn('id', $ids);

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $ws->fromArray([['ID', 'Nama', 'Kode', 'Tipe', 'PIC', 'Alamat', 'Aktif', 'Dibuat']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item['id'] ?? '',
                $item['name'] ?? '',
                $item['code'] ?? '',
                $item['type'] ?? '',
                $item['pic_name'] ?? '',
                $item['address'] ?? '',
                $item['is_active'] ? 'Ya' : 'Tidak',
                $item['created_at'] ?? '',
            ], null, 'A'.$row++);
        }

        $filename = "Lokasi_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'lokasi_');
        $writer->save($tmp);

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

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayId']);
    }

    #[On('lokasi-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[On('lokasi-created')]
    public function handleCreated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data berhasil ditambahkan.'];
    }

    #[On('lokasi-updated')]
    public function handleUpdated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data berhasil diperbarui.'];
    }

    #[On('lokasi-open-edit')]
    public function handleOpenEditFromShow(string $id): void
    {
        $this->openEdit($id);
    }

    protected function filter1Options(): array
    {
        return [
            '' => '-- Semua Tipe --',
            'warehouse' => 'Warehouse',
            'kitchen' => 'Kitchen',
            'outlet' => 'Outlet',
            'transit' => 'Transit',
        ];
    }

    protected function filter2Options(): array
    {
        return [
            '' => '-- Semua Status --',
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

        return view('livewire.holdings.resto.master.lokasi.lokasi-table', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
