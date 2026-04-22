<?php

namespace App\Livewire\Holdings\Resto\Movement\Internal;

use App\Models\Holdings\Resto\CoreStock\Rst_RequestActivity;
use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Master\Rst_MasterItem;
use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
use App\Models\Holdings\Resto\Movement\Rst_MovementItem;
use App\Services\Resto\ReferenceNumberService;
use App\Services\Resto\StockMovementService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MovementInternalTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public bool $canApprove = false;

    public bool $canApproveExcChef = false;

    public bool $canApproveRM = false;

    public bool $canApproveSPV = false;

    public bool $canInTransit = false;

    public string $search = '';

    public string $filter1 = '';

    public string $filter2 = '';

    public string $filterStatus = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'id',
        'reference_number',
        'request_number',
        'request_date',
        'from_location_id',
        'to_location_id',
        'type',
        'status',
        'pic_name',
        'created_at',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

    public ?string $receiveOverlayMode = null;

    public ?string $receiveOverlayId = null;

    public string $receiveNotes = '';

    public array $receiveItems = [];

    public ?string $rejectOverlayMode = null;

    public ?string $rejectOverlayId = null;

    public string $rejectNotes = '';

    public int $createFromLocationId = 0;

    public int $createToLocationId = 0;

    public string $createPicName = '';

    public string $createRemark = '';

    public array $createItems = [];

    public string $createRequestNumber = '';

    public string $createRequestDate = '';

    public int $editFromLocationId = 0;

    public int $editToLocationId = 0;

    public string $editPicName = '';

    public string $editRemark = '';

    public string $editRequestNumber = '';

    public string $editRequestDate = '';

    public array $editItems = [];

    public bool $showColumnPicker = false;

    public array $columnVisibility = [];

    public array $availableColumns = [
        ['key' => 'id', 'label' => 'ID', 'default' => true],
        ['key' => 'reference_number', 'label' => 'Reference', 'default' => true],
        ['key' => 'from_location_id', 'label' => 'From', 'default' => true],
        ['key' => 'to_location_id', 'label' => 'To', 'default' => true],
        ['key' => 'status', 'label' => 'Status', 'default' => true],
        ['key' => 'pic_name', 'label' => 'PIC', 'default' => true],
        ['key' => 'remark', 'label' => 'Remark', 'default' => false],
        ['key' => 'created_at', 'label' => 'Created', 'default' => false],
        ['key' => 'updated_at', 'label' => 'Updated', 'default' => false],
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'filter2' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'showDeleted' => ['except' => false],
    ];

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_DELETE') ?? false);
        $this->canApprove = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_APPROVE') ?? false);
        $this->canApproveExcChef = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_APPROVE_EXC_CHEF') ?? false);
        $this->canApproveRM = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_APPROVE_RM') ?? false);
        $this->canApproveSPV = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_APPROVE_SPV') ?? false);
        $this->canInTransit = (bool) ($u?->hasPermission('MOVEMENT_INTERNAL_IN_TRANSIT') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Master Movement', 'route' => 'dashboard.resto.master-movement', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Stock Movement', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();

        foreach ($this->availableColumns as $col) {
            $this->columnVisibility[$col['key']] = $col['default'];
        }
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_Movement::withTrashed()->with(['items.item', 'items.uom', 'fromLocation', 'toLocation']);

        if ($this->filterStatus === 'deleted') {
            $query->onlyTrashed();
        }

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('request_number', 'like', "%{$search}%")
                    ->orWhere('request_date', 'like', "%{$search}%")
                    ->orWhereHas('fromLocation', fn ($lq) => $lq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('toLocation', fn ($lq) => $lq->where('name', 'like', "%{$search}%"))
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        if ($this->filter1 !== '') {
            $query->where('status', $this->filter1);
        }

        if ($this->filter2 !== '') {
            $query->where('type', $this->filter2);
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
        if (in_array($property, ['search', 'perPage', 'sortField', 'sortDirection', 'showDeleted'], true)) {
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

    public function openShow(string $id): void
    {
        $this->overlayMode = 'show';
        $this->overlayId = $id;
    }

    public function openCreateOverlay(): void
    {
        $this->overlayMode = 'create';
        $this->overlayId = null;
        $this->createRequestNumber = 'MRQ-'.now()->format('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(4));
        $this->createRequestDate = now()->format('Y-m-d');
        $this->createItems = [
            ['item_id' => 0, 'qty' => 0, 'remark' => ''],
        ];
        $this->createFromLocationId = 0;
        $this->createToLocationId = 0;
        $this->createPicName = '';
        $this->createRemark = '';
    }

    public function openEditOverlay(string $id): void
    {
        $movement = Rst_Movement::with(['items.item', 'items.uom'])->find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if (! in_array($movement->status, ['requested'])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only edit requested movements.'];

            return;
        }

        $this->overlayMode = 'edit';
        $this->overlayId = $id;
        $this->editFromLocationId = $movement->from_location_id;
        $this->editToLocationId = $movement->to_location_id;
        $this->editPicName = $movement->pic_name ?? '';
        $this->editRemark = $movement->remark ?? '';
        $this->editRequestNumber = $movement->request_number ?? '';
        $this->editRequestDate = $movement->request_date ? $movement->request_date->format('Y-m-d') : now()->format('Y-m-d');
        $this->editItems = $movement->items->map(fn ($item) => [
            'movement_item_id' => $item->id,
            'item_id' => $item->item_id,
            'qty' => $item->qty,
            'remark' => $item->remark ?? '',
        ])->toArray();

        if (empty($this->editItems)) {
            $this->editItems = [
                ['movement_item_id' => 0, 'item_id' => 0, 'qty' => 0, 'remark' => ''],
            ];
        }
    }

    public function closeOverlay(): void
    {
        $this->reset([
            'overlayMode', 'overlayId',
            'createFromLocationId', 'createToLocationId', 'createPicName', 'createRemark', 'createItems', 'createRequestNumber', 'createRequestDate',
            'editFromLocationId', 'editToLocationId', 'editPicName', 'editRemark', 'editItems', 'editRequestNumber', 'editRequestDate',
        ]);
    }

    public function addCreateItemRow(): void
    {
        $this->createItems[] = ['item_id' => 0, 'qty' => 0, 'remark' => ''];
    }

    public function removeCreateItemRow(int $index): void
    {
        if (count($this->createItems) > 1) {
            unset($this->createItems[$index]);
            $this->createItems = array_values($this->createItems);
        }
    }

    public function onCreateFromLocationChanged(): void
    {
        $this->createItems = [
            ['item_id' => 0, 'qty' => 0, 'remark' => ''],
        ];
    }

    public function addEditItemRow(): void
    {
        $this->editItems[] = ['movement_item_id' => 0, 'item_id' => 0, 'qty' => 0, 'remark' => ''];
    }

    public function removeEditItemRow(int $index): void
    {
        if (count($this->editItems) > 1) {
            unset($this->editItems[$index]);
            $this->editItems = array_values($this->editItems);
        }
    }

    public function processCreate(): void
    {
        if ($this->createFromLocationId === 0) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select source location.'];

            return;
        }

        if ($this->createToLocationId === 0) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select destination location.'];

            return;
        }

        if ($this->createFromLocationId === $this->createToLocationId) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Source and destination cannot be the same.'];

            return;
        }

        $itemsToSave = [];
        foreach ($this->createItems as $item) {
            if ($item['item_id'] > 0 && $item['qty'] > 0) {
                $balance = Rst_StockBalance::where('item_id', (int) $item['item_id'])
                    ->where('location_id', $this->createFromLocationId)
                    ->first();

                if (! $balance || $balance->qty_available < $item['qty']) {
                    $itemName = $balance?->item?->name ?? 'Item';
                    $avail = $balance ? $balance->qty_available : 0;
                    $this->toast = ['show' => true, 'type' => 'error', 'message' => "Stok {$itemName} tidak cukup. Tersedia: {$avail}"];

                    return;
                }

                $itemsToSave[] = [
                    'item_id' => (int) $item['item_id'],
                    'qty' => (float) $item['qty'],
                    'notes' => $item['remark'] ?? null,
                ];
            }
        }

        if (empty($itemsToSave)) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select at least 1 item with qty > 0.'];

            return;
        }

        try {
            $movement = StockMovementService::createMovement(
                $this->createFromLocationId,
                $this->createToLocationId,
                $itemsToSave,
                $this->createRemark ?: null
            );

            if ($this->createRequestNumber) {
                $movement->update(['request_number' => $this->createRequestNumber, 'request_date' => $this->createRequestDate]);
            }

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement created successfully.'];
            $this->closeOverlay();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function processEdit(): void
    {
        if (! $this->overlayId) {
            return;
        }

        $movement = Rst_Movement::find($this->overlayId);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only edit requested movements.'];

            return;
        }

        if ($this->editFromLocationId === 0) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select source location.'];

            return;
        }

        if ($this->editToLocationId === 0) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select destination location.'];

            return;
        }

        if ($this->editFromLocationId === $this->editToLocationId) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Source and destination cannot be the same.'];

            return;
        }

        $itemsToSave = [];
        foreach ($this->editItems as $item) {
            if ($item['item_id'] > 0 && $item['qty'] > 0) {
                $existingItem = Rst_MovementItem::find($item['movement_item_id'] ?? 0);
                $oldQty = $existingItem ? $existingItem->qty : 0;

                if ($item['qty'] > $oldQty) {
                    $diff = $item['qty'] - $oldQty;
                    $balance = Rst_StockBalance::where('item_id', (int) $item['item_id'])
                        ->where('location_id', $this->editFromLocationId)
                        ->first();

                    if (! $balance || $balance->qty_available < $diff) {
                        $itemName = $balance?->item?->name ?? 'Item';
                        $avail = $balance ? $balance->qty_available : 0;
                        $this->toast = ['show' => true, 'type' => 'error', 'message' => "Stok {$itemName} tidak cukup untuk peningkatan qty. Tersedia: {$avail}"];

                        return;
                    }
                }

                $itemsToSave[] = [
                    'movement_item_id' => $item['movement_item_id'] ?? 0,
                    'item_id' => (int) $item['item_id'],
                    'qty' => (float) $item['qty'],
                    'remark' => $item['remark'] ?? null,
                ];
            }
        }

        if (empty($itemsToSave)) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select at least 1 item with qty > 0.'];

            return;
        }

        try {
            $movement->update([
                'from_location_id' => $this->editFromLocationId,
                'to_location_id' => $this->editToLocationId,
                'pic_name' => $this->editPicName,
                'remark' => $this->editRemark,
                'request_number' => $this->editRequestNumber,
                'request_date' => $this->editRequestDate,
            ]);

            foreach ($itemsToSave as $itemData) {
                if ($itemData['movement_item_id'] > 0) {
                    $existingItem = Rst_MovementItem::find($itemData['movement_item_id']);
                    if ($existingItem) {
                        $oldQty = $existingItem->qty;
                        $diff = $itemData['qty'] - $oldQty;

                        if ($diff != 0) {
                            $balance = Rst_StockBalance::where('item_id', $existingItem->item_id)
                                ->where('location_id', $this->editFromLocationId)
                                ->first();

                            if ($balance) {
                                $beforeReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;
                                $balance->qty_available -= $diff;
                                $balance->qty_reserved += $diff;
                                $balance->save();
                                $afterReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                                Rst_StockMutation::create([
                                    'item_id' => $existingItem->item_id,
                                    'location_id' => $this->editFromLocationId,
                                    'uom_id' => $existingItem->uom_id,
                                    'type' => 'reservation',
                                    'reference_number' => $movement->reference_number,
                                    'qty' => abs($diff),
                                    'qty_before' => $beforeReserve,
                                    'qty_after' => $afterReserve,
                                    'from_location_id' => $this->editFromLocationId,
                                    'to_location_id' => $this->editToLocationId,
                                    'user_id' => auth()->id() ?? 'SYSTEM',
                                    'notes' => "Qty adjusted from {$oldQty} to {$itemData['qty']} for movement #{$movement->id}",
                                ]);
                            }
                        }

                        $existingItem->update([
                            'qty' => $itemData['qty'],
                            'remark' => $itemData['remark'],
                        ]);
                    }
                } else {
                    $item = Rst_MasterItem::findOrFail($itemData['item_id']);

                    $newItem = Rst_MovementItem::create([
                        'movement_id' => $movement->id,
                        'item_id' => $itemData['item_id'],
                        'uom_id' => $item->uom_id,
                        'qty' => $itemData['qty'],
                        'remark' => $itemData['remark'],
                    ]);

                    $balance = Rst_StockBalance::where('item_id', $itemData['item_id'])
                        ->where('location_id', $this->editFromLocationId)
                        ->first();

                    if ($balance) {
                        $beforeReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;
                        $balance->qty_available -= $itemData['qty'];
                        $balance->qty_reserved += $itemData['qty'];
                        $balance->save();
                        $afterReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                        Rst_StockMutation::create([
                            'item_id' => $itemData['item_id'],
                            'location_id' => $this->editFromLocationId,
                            'uom_id' => $item->uom_id,
                            'type' => 'reservation',
                            'reference_number' => $movement->reference_number,
                            'qty' => $itemData['qty'],
                            'qty_before' => $beforeReserve,
                            'qty_after' => $afterReserve,
                            'from_location_id' => $this->editFromLocationId,
                            'to_location_id' => $this->editToLocationId,
                            'user_id' => auth()->id() ?? 'SYSTEM',
                            'notes' => "New item added to movement #{$movement->id}",
                        ]);
                    }
                }
            }

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement updated successfully.'];
            $this->closeOverlay();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteMovement(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if (! in_array($movement->status, ['requested'])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only delete requested movements.'];

            return;
        }

        try {
            $movementItems = Rst_MovementItem::where('movement_id', $id)->get();

            foreach ($movementItems as $movementItem) {
                $balance = Rst_StockBalance::where('item_id', $movementItem->item_id)
                    ->where('location_id', $movement->from_location_id)
                    ->first();

                if ($balance && $balance->qty_reserved >= $movementItem->qty) {
                    $beforeUnreserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;
                    $balance->qty_reserved -= $movementItem->qty;
                    $balance->qty_available += $movementItem->qty;
                    $balance->save();
                    $afterUnreserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                    $item = Rst_MasterItem::findOrFail($movementItem->item_id);

                    Rst_StockMutation::create([
                        'item_id' => $movementItem->item_id,
                        'location_id' => $movement->from_location_id,
                        'uom_id' => $item->uom_id,
                        'type' => 'unreserved',
                        'reference_number' => $movement->reference_number,
                        'qty' => $movementItem->qty,
                        'qty_before' => $beforeUnreserve,
                        'qty_after' => $afterUnreserve,
                        'from_location_id' => $movement->from_location_id,
                        'to_location_id' => $movement->to_location_id,
                        'user_id' => auth()->id() ?? 'SYSTEM',
                        'notes' => "Unreserved for deleted movement #{$movement->id}",
                    ]);
                }
            }

            $movement->delete();

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement deleted. Stock restored.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function restoreMovement(string $id): void
    {
        $movement = Rst_Movement::withTrashed()->find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if (! $movement->trashed()) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Movement is not deleted.'];

            return;
        }

        try {
            $movement->restore();

            $movementItems = Rst_MovementItem::where('movement_id', $id)->get();

            foreach ($movementItems as $movementItem) {
                $balance = Rst_StockBalance::where('item_id', $movementItem->item_id)
                    ->where('location_id', $movement->from_location_id)
                    ->first();

                if ($balance) {
                    $beforeReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;
                    $balance->qty_available -= $movementItem->qty;
                    $balance->qty_reserved += $movementItem->qty;
                    $balance->save();
                    $afterReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                    $item = Rst_MasterItem::findOrFail($movementItem->item_id);

                    Rst_StockMutation::create([
                        'item_id' => $movementItem->item_id,
                        'location_id' => $movement->from_location_id,
                        'uom_id' => $item->uom_id,
                        'type' => 'reservation',
                        'reference_number' => $movement->reference_number,
                        'qty' => $movementItem->qty,
                        'qty_before' => $beforeReserve,
                        'qty_after' => $afterReserve,
                        'from_location_id' => $movement->from_location_id,
                        'to_location_id' => $movement->to_location_id,
                        'user_id' => auth()->id() ?? 'SYSTEM',
                        'notes' => "Restored reservation for movement #{$movement->id}",
                    ]);
                }
            }

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement restored successfully.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function cloneMovement(string $id): void
    {
        $movement = Rst_Movement::with(['items.item', 'items.uom'])->find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        try {
            $newMovement = Rst_Movement::create([
                'reference_number' => ReferenceNumberService::generateMovementNumber(),
                'request_number' => 'MRQ-'.now()->format('Ymd').'-'.strtoupper(\Illuminate\Support\Str::random(4)),
                'request_date' => now()->format('Y-m-d'),
                'from_location_id' => $movement->from_location_id,
                'to_location_id' => $movement->to_location_id,
                'type' => 'internal_transfer',
                'status' => 'requested',
                'pic_name' => $movement->pic_name,
                'remark' => $movement->remark,
                'approval_level' => 0,
            ]);

            foreach ($movement->items as $item) {
                Rst_MovementItem::create([
                    'movement_id' => $newMovement->id,
                    'item_id' => $item->item_id,
                    'uom_id' => $item->uom_id,
                    'qty' => $item->qty,
                    'remark' => $item->remark,
                ]);

                $balance = Rst_StockBalance::where('item_id', $item->item_id)
                    ->where('location_id', $movement->from_location_id)
                    ->first();

                if ($balance) {
                    $beforeReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;
                    $balance->qty_available -= $item->qty;
                    $balance->qty_reserved += $item->qty;
                    $balance->save();
                    $afterReserve = $balance->qty_available + $balance->qty_reserved + $balance->qty_in_transit;

                    Rst_StockMutation::create([
                        'item_id' => $item->item_id,
                        'location_id' => $movement->from_location_id,
                        'uom_id' => $item->uom_id,
                        'type' => 'reservation',
                        'reference_number' => $newMovement->reference_number,
                        'qty' => $item->qty,
                        'qty_before' => $beforeReserve,
                        'qty_after' => $afterReserve,
                        'from_location_id' => $movement->from_location_id,
                        'to_location_id' => $movement->to_location_id,
                        'user_id' => auth()->id() ?? 'SYSTEM',
                        'notes' => "Cloned from movement #{$movement->id}",
                    ]);
                }
            }

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement cloned successfully.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function openRejectOverlay(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if (! in_array($movement->status, ['requested', 'approved'])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only reject on Requested or Approved status.'];

            return;
        }

        $this->rejectOverlayMode = 'reject';
        $this->rejectOverlayId = $id;
        $this->rejectNotes = '';
    }

    public function closeRejectOverlay(): void
    {
        $this->rejectOverlayMode = null;
        $this->rejectOverlayId = null;
        $this->rejectNotes = '';
    }

    public function excChefCanApprove(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only approve on Requested status.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 0) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Exc Chef already approved. Contact RM.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'Exc Chef';
            StockMovementService::approveMovement((int) $id, 1, $approverName, 'Approved by Exc Chef');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by Exc Chef.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rmCanApprove(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only approve on Requested status.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 1) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Not yet approved by Exc Chef.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'RM';
            StockMovementService::approveMovement((int) $id, 2, $approverName, 'Approved by RM');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function spvCanApprove(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only approve on Requested status.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 2) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Not yet approved by RM.'];

            return;
        }

        try {
            $approverName = auth()->user()?->name ?? 'Supervisor';
            StockMovementService::approveMovement((int) $id, 3, $approverName, 'Approved by SPV');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Approved by SPV. Movement fully approved.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function excChefCanReject(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if (! in_array($movement->status, ['requested', 'approved'])) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only reject on Requested or Approved status.'];

            return;
        }

        try {
            $rejecterName = auth()->user()?->name ?? 'Exc Chef';
            $reason = $this->rejectNotes ?: 'Rejected by Exc Chef';
            StockMovementService::rejectMovement((int) $id, $rejecterName, $reason);
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Movement rejected, stock restored.'];
            $this->closeRejectOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function dispatchItems(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if ($movement->status !== 'approved') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only dispatch on Approved status.'];

            return;
        }

        try {
            StockMovementService::dispatchItems((int) $id);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Items dispatched (In Transit).'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function openReceiveOverlay(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if ($movement->status !== 'in_transit') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only receive on In Transit status.'];

            return;
        }

        $this->receiveOverlayMode = 'receive';
        $this->receiveOverlayId = $id;
        $this->receiveNotes = '';

        $items = Rst_MovementItem::where('movement_id', $id)->with(['item', 'uom'])->get();
        $this->receiveItems = $items->map(fn ($item) => [
            'movement_item_id' => $item->id,
            'item_id' => $item->item_id,
            'item_name' => $item->item?->name ?? '-',
            'uom_name' => $item->uom?->name ?? '-',
            'qty_requested' => $item->qty,
            'qty_received' => $item->qty,
        ])->toArray();
    }

    public function closeReceiveOverlay(): void
    {
        $this->receiveOverlayMode = null;
        $this->receiveOverlayId = null;
        $this->receiveNotes = '';
        $this->receiveItems = [];
    }

    public function processReceive(): void
    {
        if (! $this->receiveOverlayId) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Invalid ID.'];

            return;
        }

        $movement = Rst_Movement::find($this->receiveOverlayId);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data not found.'];

            return;
        }

        if ($movement->status !== 'in_transit') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Can only receive on In Transit status.'];

            return;
        }

        try {
            $itemsWithQty = collect($this->receiveItems)->mapWithKeys(fn ($item) => [
                $item['movement_item_id'] => ['qty_received' => (float) $item['qty_received']],
            ])->toArray();

            StockMovementService::receiveItems(
                (int) $this->receiveOverlayId,
                $this->receiveNotes,
                $itemsWithQty
            );

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Items received and completed.'];
            $this->closeReceiveOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function exportData()
    {
        $data = $this->dataQuery();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $headers = ['Ref No', 'Request No', 'Request Date', 'From', 'To', 'Status', 'PIC', 'Remark', 'Created At'];
        $ws->fromArray([$headers], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->reference_number ?? '-',
                $item->request_number ?? '-',
                $item->request_date ? $item->request_date->format('Y-m-d') : '-',
                $item->fromLocation?->name ?? '-',
                $item->toLocation?->name ?? '-',
                $item->status ?? '-',
                $item->pic_name ?? '-',
                $item->remark ?? '-',
                $item->created_at ? $item->created_at->format('Y-m-d H:i') : '-',
            ], null, 'A'.$row++);
        }

        $filename = 'StockMovement_'.now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'stockmovement_');
        $writer->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    public function getCreateFromLocations(): array
    {
        $locations = Rst_MasterLokasi::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($loc) => ['id' => $loc->id, 'name' => $loc->name])
            ->toArray();

        return $locations;
    }

    public function getCreateToLocations(): array
    {
        $locations = Rst_MasterLokasi::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($loc) => ['id' => $loc->id, 'name' => $loc->name])
            ->toArray();

        return $locations;
    }

    public function getCreateAvailableItems(): array
    {
        if ($this->createFromLocationId === 0) {
            return [];
        }

        $balances = Rst_StockBalance::where('location_id', $this->createFromLocationId)
            ->where('qty_available', '>', 0)
            ->with(['item', 'uom'])
            ->orderBy('item_id')
            ->get();

        $result = $balances->map(fn ($balance) => [
            'id' => $balance->item_id,
            'name' => $balance->item?->name ?? '-',
            'sku' => $balance->item?->sku ?? '-',
            'uom_id' => $balance->item?->uom_id ?? 0,
            'uom_symbols' => $balance->uom?->symbols ?? '-',
            'available_qty' => $balance->qty_available,
        ])->toArray();

        return $result;
    }

    public function getEditAvailableItems(): array
    {
        if ($this->editFromLocationId === 0) {
            return [];
        }

        $balances = Rst_StockBalance::where('location_id', $this->editFromLocationId)
            ->where('qty_available', '>', 0)
            ->with(['item', 'uom'])
            ->orderBy('item_id')
            ->get();

        $result = $balances->map(fn ($balance) => [
            'id' => $balance->item_id,
            'name' => $balance->item?->name ?? '-',
            'sku' => $balance->item?->sku ?? '-',
            'uom_id' => $balance->item?->uom_id ?? 0,
            'uom_symbols' => $balance->uom?->symbols ?? '-',
            'available_qty' => $balance->qty_available,
        ])->toArray();

        return $result;
    }

    #[On('movement-internal-2-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    public function getDetailData(): ?Rst_Movement
    {
        if (! $this->overlayId) {
            return null;
        }

        return Rst_Movement::with(['items.item', 'items.uom', 'fromLocation', 'toLocation'])
            ->find($this->overlayId);
    }

    public function getStockMutations(): Collection
    {
        $movement = $this->getDetailData();
        if (! $movement || ! $movement->reference_number) {
            return collect();
        }

        return Rst_StockMutation::where('reference_number', $movement->reference_number)
            ->with(['item', 'uom', 'location', 'fromLocation', 'toLocation'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getRequestActivities(): Collection
    {
        $movement = $this->getDetailData();
        if (! $movement) {
            return collect();
        }

        return Rst_RequestActivity::where('movement_id', $movement->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function toggleColumn(string $column): void
    {
        if (isset($this->columnVisibility[$column])) {
            $this->columnVisibility[$column] = ! $this->columnVisibility[$column];
        }
    }

    protected function filter1Options(): array
    {
        return [
            '' => '-- Semua Status --',
            'draft' => 'Draft',
            'requested' => 'Requested',
            'approved' => 'Approved',
            'in_transit' => 'In Transit',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            'failed' => 'Failed',
        ];
    }

    protected function filter2Options(): array
    {
        return [
            '' => '-- All Types --',
            'internal_transfer' => 'Internal Transfer',
        ];
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

    public function deleteItem(string $id): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin delete.'];

            return;
        }

        $item = Rst_Movement::withTrashed()->find($id);

        if (! $item) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        $item->delete();

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement berhasil dihapus.'];
    }

    public function restoreItem(string $id): void
    {
        if (! $this->canDelete) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin restore.'];

            return;
        }

        $item = Rst_Movement::onlyTrashed()->find($id);

        if (! $item) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        $item->restore();

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement berhasil di-restore.'];
    }

    public function cloneItem(string $id): void
    {
        if (! $this->canCreate) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak punya izin clone.'];

            return;
        }

        $original = Rst_Movement::with('items')->find($id);

        if (! $original) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        $clone = $original->replicate();
        $clone->reference_number = \App\Services\Resto\ReferenceNumberService::generateMovementNumber();
        $clone->status = 'requested';
        $clone->approval_level = 0;
        $clone->exc_chef_approved_by = null;
        $clone->exc_chef_approved_at = null;
        $clone->rm_approved_by = null;
        $clone->rm_approved_at = null;
        $clone->spv_approved_by = null;
        $clone->spv_approved_at = null;
        $clone->save();

        foreach ($original->items as $item) {
            $cloneItem = $item->replicate();
            $cloneItem->movement_id = $clone->id;
            $cloneItem->save();
        }

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement berhasil di-clone.'];
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
        $data = Rst_Movement::withTrashed()->whereIn('id', $ids)->get();

        return $this->generateExcel($data, 'Selected');
    }

    private function generateExcel(Collection $data, string $type)
    {
        $spreadsheet = new Spreadsheet;
        $ws = $spreadsheet->getActiveSheet();

        $headers = ['ID', 'Reference No', 'From', 'To', 'Status', 'PIC', 'Remark', 'Created'];
        $ws->fromArray([$headers], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item->id,
                $item->reference_number ?? '',
                $item->fromLocation?->name ?? '-',
                $item->toLocation?->name ?? '-',
                $item->status,
                $item->pic_name ?? '-',
                $item->remark ?? '-',
                $item->created_at?->format('Y-m-d H:i:s') ?? '',
            ], null, 'A'.$row++);
        }

        foreach (range('A', 'H') as $col) {
            $ws->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Movement_{$type}_".now()->format('Ymd_His').'.xlsx';

        $tmp = tempnam(sys_get_temp_dir(), 'movement_');
        (new Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $filename)->deleteFileAfterSend(true);
    }

    public function render()
    {
        $data = $this->paginateCollection($this->dataQuery(), $this->perPage);

        $visible = $data->getCollection()
            ->pluck('id')
            ->map(fn ($v) => (string) $v)
            ->toArray();

        $this->selectAll = count($visible) > 0 && empty(array_diff($visible, $this->selectedItems));

        return view('livewire.holdings.resto.movement.internal.movement-internal-table-2', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
