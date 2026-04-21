<?php

namespace App\Livewire\Holdings\Resto\CoreStock\Stock;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Master\Rst_MasterKategori;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class StockLocationDetail extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public int $locationId;

    public ?Rst_MasterLokasi $location = null;

    public string $search = '';

    public string $filter1 = '';

    public int $perPage = 10;

    public string $sortField = 'item_name';

    public string $sortDirection = 'asc';

    public int $totalAll = 0;

    protected array $allowedSortFields = [
        'item_name',
        'item_sku',
        'category_name',
        'qty_available',
        'qty_reserved',
        'qty_in_transit',
        'qty_waste',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

    public array $detailData = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'item_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(int $id): void
    {
        $this->locationId = $id;
        $this->location = Rst_MasterLokasi::find($id);

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Core Stock', 'route' => 'dashboard.resto.core-stock', 'color' => 'text-gray-800'],
            ['label' => 'Stock per Location', 'route' => 'dashboard.resto.stock-location', 'color' => 'text-gray-800'],
            ['label' => $this->location?->name ?? 'Detail', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->totalAll = Rst_StockBalance::where('location_id', $id)->count();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_StockBalance::with(['item', 'item.category', 'uom'])
            ->where('location_id', $this->locationId);

        if ($this->search !== '') {
            $search = $this->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($this->filter1 !== '') {
            $query->whereHas('item', function ($q) {
                $q->where('category_id', $this->filter1);
            });
        }

        return $query->get()->map(function ($item) {
            return (object) [
                'item_id' => $item->item_id,
                'item_id_str' => (string) $item->item_id,
                'item_name' => $item->item?->name ?? '-',
                'item_sku' => $item->item?->sku ?? '-',
                'category_name' => $item->item?->category?->name ?? '-',
                'qty_available' => $item->qty_available,
                'qty_reserved' => $item->qty_reserved,
                'qty_in_transit' => $item->qty_in_transit,
                'qty_waste' => $item->qty_waste,
                'item_min_stock' => $item->item?->min_stock ?? 0,
                'item' => $item->item,
            ];
        })->sortBy($this->sortField, SORT_REGULAR, $this->sortDirection === 'desc');
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
        $this->reset(['search', 'filter1']);
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

    protected function visibleIds(): array
    {
        $p = $this->paginateCollection($this->dataQuery(), $this->perPage);

        return $p->getCollection()
            ->pluck('item_id_str')
            ->toArray();
    }

    public function exportFiltered()
    {
        $data = $this->dataQuery();

        return $this->generateExcel($data, 'Filtered');
    }

    public function exportSelected()
    {
        if (empty($this->selectedItems)) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Please select data first'];

            return null;
        }

        $ids = array_values(array_unique(array_map('strval', $this->selectedItems)));
        $data = $this->dataQuery()->filter(fn ($item) => in_array((string) $item->item_id, $ids));

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $ws->fromArray([['Item', 'SKU', 'Category', 'Qty Available', 'Qty Reserved', 'Qty In Transit', 'Qty Waste']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->item_name ?? '-',
                $item->item_sku ?? '-',
                $item->category_name ?? '-',
                $item->qty_available ?? '0',
                $item->qty_reserved ?? '0',
                $item->qty_in_transit ?? '0',
                $item->qty_waste ?? '0',
            ], null, 'A'.$row++);
        }

        $filename = "StockLocationDetail_{$this->locationId}_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'stocklocationdetail_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    public function openDetail(string $itemId): void
    {
        $balance = Rst_StockBalance::with(['item', 'item.category', 'uom'])
            ->where('location_id', $this->locationId)
            ->where('item_id', $itemId)
            ->first();

        if (! $balance) {
            return;
        }

        $mutations = Rst_StockMutation::where('item_id', $itemId)
            ->where('location_id', $this->locationId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $this->detailData = [
            'balance' => $balance,
            'mutations' => $mutations,
        ];

        $this->overlayMode = 'detail';
        $this->overlayId = $itemId;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayId', 'detailData']);
    }

    protected function filter1Options(): array
    {
        return Rst_MasterKategori::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        $data = $this->paginateCollection($this->dataQuery(), $this->perPage);

        $this->selectAll = count($this->visibleIds()) > 0 && empty(array_diff($this->visibleIds(), $this->selectedItems));

        return view('livewire.holdings.resto.core-stock.stock.stock-location-detail', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'location' => $this->location,
        ])->layout('components.sccr-layout');
    }
}
