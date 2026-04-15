<?php

namespace App\Livewire\Holdings\Resto\Resep\Menu;

use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Resep\Rst_StockRepack;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ResepMenuTable extends Component
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
        'repack_number',
        'source_item_id',
        'target_item_id',
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

    public function getDetailData(): ?Rst_StockRepack
    {
        if (! $this->overlayId) {
            return null;
        }

        return Rst_StockRepack::with(['sourceItem', 'targetItem', 'location', 'creator'])
            ->find($this->overlayId);
    }

    public function getStockMutations(): Collection
    {
        $detail = $this->getDetailData();
        if (! $detail || ! $detail->repack_number) {
            return collect();
        }

        return Rst_StockMutation::where('reference_number', $detail->repack_number)
            ->with(['item', 'uom', 'location'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getRepackOutMutation(): ?Rst_StockMutation
    {
        return $this->getStockMutations()->where('type', 'repack_out')->first();
    }

    public function getRepackInMutation(): ?Rst_StockMutation
    {
        return $this->getStockMutations()->where('type', 'repack_in')->first();
    }

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
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Repack Stok', 'route' => 'dashboard.resto.resep', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Repack Stok', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery()
    {
        return Rst_StockRepack::query()
            ->with(['sourceItem', 'targetItem', 'location', 'creator'])
            ->get();
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

        $ws->fromArray([['No. Repack', 'Item Sumber', 'Item Target', 'Qty Sumber', 'Multiplier', 'Qty Hasil', 'Tanggal']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->repack_number ?? '',
                $item->sourceItem?->name ?? '',
                $item->targetItem?->name ?? '',
                $item->qty_source_taken ?? '',
                $item->multiplier ?? '',
                $item->qty_target_result ?? '',
                $item->created_at?->format('Y-m-d H:i') ?? '',
            ], null, 'A'.$row++);
        }

        $filename = "Repack_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'satuan_');
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

    #[On('repack-created')]
    public function handleCreated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Repack berhasil ditambahkan.'];
    }

    #[On('repack-updated')]
    public function handleUpdated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Repack berhasil diperbarui.'];
    }

    #[On('repack-open-edit')]
    public function handleOpenEditFromShow(string $id): void
    {
        $this->openEdit($id);
    }

    #[On('repack-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    protected function filter1Options(): array
    {
        return [
            '' => '-- Semua Status --',
            '1' => 'Aktif',
            '0' => 'Nonaktif',
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

        $detail = $this->overlayMode === 'show' ? $this->getDetailData() : null;
        $stockMutations = $this->overlayMode === 'show' ? $this->getStockMutations() : collect();
        $repackOut = $this->overlayMode === 'show' ? $this->getRepackOutMutation() : null;
        $repackIn = $this->overlayMode === 'show' ? $this->getRepackInMutation() : null;

        return view('livewire.holdings.resto.resep.menu.resep-menu-table', [

            'data' => $data,
            'detail' => $detail,
            'stockMutations' => $stockMutations,
            'repackOut' => $repackOut,
            'repackIn' => $repackIn,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
