<?php

namespace App\Livewire\Holdings\Resto\CoreStock\Stock;

use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class StockMutationTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    public string $filter1 = '';

    public string $filter2 = '';

    public string $filter3 = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public int $totalAll = 0;

    protected array $allowedSortFields = [
        'created_at',
        'item_name',
        'location_name',
        'type',
        'qty',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'filter2' => ['except' => ''],
        'filter3' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Core Stock', 'route' => 'dashboard.resto.core-stock', 'color' => 'text-gray-800'],
            ['label' => 'Stock Mutation', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->totalAll = Rst_StockMutation::count();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_StockMutation::with(['item', 'item.category', 'location', 'fromLocation', 'toLocation', 'uom']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })->orWhereHas('location', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })->orWhere('reference_type', 'like', "%{$search}%")
                    ->orWhere('reference_id', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($this->filter1 !== '') {
            $query->where('type', $this->filter1);
        }

        if ($this->filter2 !== '') {
            $query->where('location_id', $this->filter2);
        }

        if ($this->filter3 !== '') {
            $query->where('item_id', $this->filter3);
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
        $this->reset(['search', 'filter1', 'filter2', 'filter3']);
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

        $ws->fromArray([['ID', 'Tanggal', 'Item', 'SKU', 'Lokasi', 'Tipe', 'Qty', 'Qty Before', 'Qty After', 'Reference', 'Dari Lokasi', 'Ke Lokasi', 'Notes', 'Satuan']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->id ?? '',
                $item->created_at?->format('Y-m-d H:i:s') ?? '-',
                $item->item?->name ?? '-',
                $item->item?->sku ?? '-',
                $item->location?->name ?? '-',
                $item->type ?? '-',
                $item->qty ?? '0',
                $item->qty_before ?? '0',
                $item->qty_after ?? '0',
                ($item->reference_type ?? '-').' #'.($item->reference_id ?? '-'),
                $item->fromLocation?->name ?? '-',
                $item->toLocation?->name ?? '-',
                $item->notes ?? '-',
                $item->uom?->name ?? '-',
            ], null, 'A'.$row++);
        }

        $filename = "StockMutation_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'stockmutation_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    protected function filter1Options(): array
    {
        return [
            'in' => 'IN (Penerimaan)',
            'out' => 'OUT (Pengeluaran)',
            'transfer_in' => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            'adjustment' => 'Adjustment',
            'reserve' => 'Reserve',
            'unreserve' => 'Unreserve',
            'consume' => 'Consume',
            'waste' => 'Waste',
        ];
    }

    protected function filter2Options(): array
    {
        return Rst_MasterLokasi::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function filter3Options(): array
    {
        return Rst_MasterItem::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function render()
    {
        $data = $this->paginateCollection($this->dataQuery(), $this->perPage);

        $visible = $data->getCollection()
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));

        return view('livewire.holdings.resto.core-stock.stock.stock-mutation', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
            'filter3Options' => $this->filter3Options(),
        ])->layout('components.sccr-layout');
    }
}
