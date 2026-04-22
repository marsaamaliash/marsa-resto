<?php

namespace App\Livewire\Holdings\Resto\CoreStock\Stock;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterKategori;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class StockMinimalTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    public string $filter1 = '';

    public int $perPage = 10;

    public string $sortField = 'selisih';

    public string $sortDirection = 'desc';

    public int $totalAll = 0;

    protected array $allowedSortFields = [
        'item_name',
        'qty_available',
        'min_stock',
        'selisih',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'selisih'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Core Stock', 'route' => 'dashboard.resto.core-stock', 'color' => 'text-gray-800'],
            ['label' => 'Critical Stock', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->totalAll = Rst_MasterItem::where('min_stock', '>', 0)->count();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_StockBalance::with(['item', 'item.category', 'item.uom', 'location'])
            ->select(
                'item_id',
                Rst_StockBalance::raw('SUM(qty_available) as total_available')
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

            $minStock = $balance?->item?->min_stock ?? 0;
            $qtyAvailable = $item->total_available ?? 0;

            return (object) [
                'item_id' => $item->item_id,
                'item' => $balance?->item,
                'qty_available' => $qtyAvailable,
                'min_stock' => $minStock,
                'selisih' => $minStock - $qtyAvailable,
                'status' => $this->getStatus($qtyAvailable, $minStock),
            ];
        })
            ->filter(fn ($item) => $item->min_stock > 0 && $item->qty_available <= $item->min_stock * 1.2);

        if (in_array($this->sortField, $this->allowedSortFields, true)) {
            $results = $results->sortBy(function ($item) {
                return match ($this->sortField) {
                    'qty_available' => (float) $item->qty_available,
                    'min_stock' => (float) $item->min_stock,
                    'selisih' => (float) $item->selisih,
                    'item_name' => $item->item?->name ?? '',
                    default => $item->item_id,
                };
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        return $results->values();
    }

    private function getStatus(float $qtyAvailable, float $minStock): string
    {
        if ($qtyAvailable <= $minStock) {
            return 'critical';
        }
        if ($qtyAvailable <= $minStock * 1.2) {
            return 'warning';
        }

        return 'normal';
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

        $ws->fromArray([['ID', 'Item', 'SKU', 'Category', 'Current Stock', 'Min Stock', 'Difference', 'Status', 'Unit']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->item_id ?? '',
                $item->item?->name ?? '-',
                $item->item?->sku ?? '-',
                $item->item?->category?->name ?? '-',
                $item->qty_available ?? '0',
                $item->min_stock ?? '0',
                $item->selisih ?? '0',
                $item->status === 'critical' ? 'Critical' : 'Warning',
                $item->item?->uom?->name ?? '-',
            ], null, 'A'.$row++);
        }

        $filename = "StockMinimal_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'stockminimal_');
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

        return view('livewire.holdings.resto.core-stock.stock.stock-minimal', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
        ])->layout('components.sccr-layout');
    }
}
