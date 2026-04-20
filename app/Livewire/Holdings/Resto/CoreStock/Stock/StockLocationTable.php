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

class StockLocationTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public string $search = '';

    public string $filter1 = '';

    public string $filter2 = '';

    public int $perPage = 10;

    public string $sortField = 'location_name';

    public string $sortDirection = 'asc';

    public int $totalAll = 0;

    protected array $allowedSortFields = [
        'location_name',
        'total_available',
        'total_reserved',
        'total_in_transit',
        'total_waste',
        'total_items',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

    public array $detailData = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'filter2' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'location_name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Core Stock', 'route' => 'dashboard.resto.core-stock', 'color' => 'text-gray-800'],
            ['label' => 'Stok per Lokasi', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->totalAll = Rst_MasterLokasi::count();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_StockBalance::with(['item', 'item.category', 'location', 'uom']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('location', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                    ->orWhereHas('item', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->filter1 !== '') {
            $query->where('location_id', $this->filter1);
        }

        if ($this->filter2 !== '') {
            $query->whereHas('item', function ($q) {
                $q->where('category_id', $this->filter2);
            });
        }

        $results = $query->get();

        $grouped = $results->groupBy('location_id')->map(function ($items, $locationId) {
            $location = $items->first()->location;
            $locationName = $location?->name ?? '-';
            $locationIdStr = (string) $locationId;

            return (object) [
                'location_id' => $locationId,
                'location_id_str' => $locationIdStr,
                'location_name' => $locationName,
                'location' => $location,
                'items' => $items,
                'total_items' => $items->count(),
                'total_stock' => $items->sum('qty_available'),
                'total_critical' => $items->filter(function ($item) {
                    $minStock = $item->item?->min_stock ?? 0;

                    return $item->qty_available <= $minStock;
                })->count(),
            ];
        })->values();

        if ($this->sortField === 'location_name') {
            $grouped = $grouped->sortBy('location_name', SORT_REGULAR, $this->sortDirection === 'desc');
        } elseif (in_array($this->sortField, ['total_available', 'total_reserved', 'total_in_transit', 'total_waste'], true)) {
            $grouped = $grouped->sortBy(function ($loc) {
                return match ($this->sortField) {
                    'total_available' => $loc->items->sum('qty_available'),
                    'total_reserved' => $loc->items->sum('qty_reserved'),
                    'total_in_transit' => $loc->items->sum('qty_in_transit'),
                    'total_waste' => $loc->items->sum('qty_waste'),
                    default => 0,
                };
            }, SORT_REGULAR, $this->sortDirection === 'desc');
        }

        $flattened = collect();

        foreach ($grouped as $locationGroup) {
            $flattened->push((object) [
                'row_type' => 'location_header',
                'location_id' => $locationGroup->location_id,
                'location_id_str' => $locationGroup->location_id_str,
                'location_name' => $locationGroup->location_name,
                'total_items' => $locationGroup->total_items,
                'total_stock' => $locationGroup->total_stock,
                'total_critical' => $locationGroup->total_critical,
            ]);
        }

        return $flattened;
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
            ->filter(fn ($item) => $item->row_type === 'location_header')
            ->pluck('location_id_str')
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
        $data = $this->dataQuery()->filter(fn ($item) => in_array((string) $item->location_id, $ids));

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel($data, string $type)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $ws->fromArray([['Lokasi', 'Qty Available', 'Qty Reserved', 'Qty In Transit', 'Qty Waste', 'Jumlah Item']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->location_name ?? '-',
                $item->total_available ?? '0',
                $item->total_reserved ?? '0',
                $item->total_in_transit ?? '0',
                $item->total_waste ?? '0',
                $item->total_items ?? '0',
            ], null, 'A'.$row++);
        }

        $filename = "StockLocation_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'stocklocation_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    public function openDetail(string $locationId): void
    {
        $location = Rst_MasterLokasi::find($locationId);
        $balances = Rst_StockBalance::with(['item', 'item.category', 'uom'])
            ->where('location_id', $locationId)
            ->get();

        $itemsWithMutations = $balances->map(function ($balance) {
            $mutations = Rst_StockMutation::where('item_id', $balance->item_id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return (object) [
                'balance' => $balance,
                'mutations' => $mutations,
            ];
        });

        $this->detailData = [
            'location' => $location,
            'items' => $itemsWithMutations,
        ];

        $this->overlayMode = 'detail';
        $this->overlayId = $locationId;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayId', 'detailData']);
    }

    public function toggleLocationSelect(string $locationId): void
    {
        $locId = (string) $locationId;
        $itemsInLoc = $this->getItemsInLocation($locId);
        $itemIds = $itemsInLoc->pluck('item_id_str')->toArray();

        $allSelected = ! empty(array_diff($itemIds, $this->selectedItems));

        if ($allSelected) {
            $this->selectedItems = array_values(array_diff($this->selectedItems, $itemIds));
        } else {
            $this->selectedItems = array_values(array_unique(array_merge($this->selectedItems, $itemIds)));
        }
    }

    public function isLocationAllSelected(string $locationId): bool
    {
        $locId = (string) $locationId;
        $itemsInLoc = $this->getItemsInLocation($locId);
        $itemIds = $itemsInLoc->pluck('item_id_str')->toArray();

        return ! empty($itemIds) && empty(array_diff($itemIds, $this->selectedItems));
    }

    public function isLocationPartiallySelected(string $locationId): bool
    {
        $locId = (string) $locationId;
        $itemsInLoc = $this->getItemsInLocation($locId);
        $itemIds = $itemsInLoc->pluck('item_id_str')->toArray();

        $selected = array_intersect($itemIds, $this->selectedItems);

        return ! empty($selected) && count($selected) < count($itemIds);
    }

    private function getItemsInLocation(string $locationId): Collection
    {
        return Rst_StockBalance::where('location_id', $locationId)
            ->get()
            ->map(fn ($item) => (object) [
                'item_id_str' => (string) $item->item_id,
            ]);
    }

    protected function filter1Options(): array
    {
        return Rst_MasterLokasi::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function filter2Options(): array
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
            ->filter(fn ($item) => $item->row_type === 'location_header')
            ->pluck('location_id_str')
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));

        return view('livewire.holdings.resto.core-stock.stock.stock-location', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
