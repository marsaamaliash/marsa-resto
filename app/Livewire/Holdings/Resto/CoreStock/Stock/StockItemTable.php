<?php

namespace App\Livewire\Holdings\Resto\CoreStock\Stock;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterKategori;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class StockItemTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    public string $filter1 = '';

    public int $perPage = 10;

    public string $sortField = 'item_name';

    public string $sortDirection = 'asc';

    public int $totalAll = 0;

    protected array $allowedSortFields = [
        'item_name',
        'item_sku',
        'total_available',
        'total_reserved',
        'total_in_transit',
        'total_waste',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'item_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Core Stock', 'route' => 'dashboard.resto.core-stock', 'color' => 'text-gray-800'],
            ['label' => 'Item Stock', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->totalAll = Rst_MasterItem::count();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_StockBalance::with(['item', 'item.category', 'item.uom'])
            ->select(
                'item_id',
                Rst_StockBalance::raw('SUM(qty_available) as total_available'),
                Rst_StockBalance::raw('SUM(qty_reserved) as total_reserved'),
                Rst_StockBalance::raw('SUM(qty_in_transit) as total_in_transit'),
                Rst_StockBalance::raw('SUM(qty_waste) as total_waste')
            )
            ->groupBy('item_id');

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

        $results = $query->get();

        $results = $results->map(function ($item) {
            $balance = Rst_StockBalance::with(['item', 'item.category', 'item.uom'])
                ->where('item_id', $item->item_id)
                ->first();

            return (object) [
                'item_id' => $item->item_id,
                'item' => $balance?->item,
                'total_available' => $item->total_available,
                'total_reserved' => $item->total_reserved,
                'total_in_transit' => $item->total_in_transit,
                'total_waste' => $item->total_waste,
                'total_qty' => ($item->total_available ?? 0) + ($item->total_reserved ?? 0) + ($item->total_in_transit ?? 0) + ($item->total_waste ?? 0),
            ];
        });

        if (in_array($this->sortField, $this->allowedSortFields, true)) {
            $results = $results->sortBy(function ($item) {
                return match ($this->sortField) {
                    'total_available' => (float) $item->total_available,
                    'total_reserved' => (float) $item->total_reserved,
                    'total_in_transit' => (float) $item->total_in_transit,
                    'total_waste' => (float) $item->total_waste,
                    'item_name' => $item->item?->name ?? '',
                    'item_sku' => $item->item?->sku ?? '',
                    default => $item->item_id,
                };
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        return $results->values();
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
            ->pluck('item_id')
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

        $ws->fromArray([['ID', 'Item', 'SKU', 'Category', 'Qty Available', 'Qty Reserved', 'Qty In Transit', 'Qty Waste', 'Total Qty', 'Unit']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->item_id ?? '',
                $item->item?->name ?? '-',
                $item->item?->sku ?? '-',
                $item->item?->category?->name ?? '-',
                $item->total_available ?? '0',
                $item->total_reserved ?? '0',
                $item->total_in_transit ?? '0',
                $item->total_waste ?? '0',
                $item->total_qty ?? '0',
                $item->item?->uom?->name ?? '-',
            ], null, 'A'.$row++);
        }

        $filename = "StockItem_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'stockitem_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
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

        $visible = $data->getCollection()
            ->pluck('item_id')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));

        return view('livewire.holdings.resto.core-stock.stock.stock-item', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
        ])->layout('components.sccr-layout');
    }
}
