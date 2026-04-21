<?php

namespace App\Livewire\Holdings\Resto\Movement\Internal;

use App\Models\Holdings\Resto\CoreStock\Rst_RequestActivity;
use App\Models\Holdings\Resto\CoreStock\Rst_StockMutation;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
use App\Models\Holdings\Resto\Movement\Rst_MovementItem;
use App\Services\Resto\StockMovementService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

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

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'id',
        'reference_number',
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

    protected $queryString = [
        'search' => ['except' => ''],
        'filter1' => ['except' => ''],
        'filter2' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
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
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_Movement::with(['items.item', 'items.uom', 'fromLocation', 'toLocation']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
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

    public function openShow(string $id): void
    {
        $this->overlayMode = 'show';
        $this->overlayId = $id;
    }

    public function openCreateOverlay(): void
    {
        $this->overlayMode = 'create';
        $this->overlayId = null;
    }

    public function closeOverlay(): void
    {
        $this->reset(['overlayMode', 'overlayId', 'createFromLocationId', 'createToLocationId', 'createPicName', 'createRemark', 'createItems']);
    }

    public function initCreateItems(): void
    {
        if (empty($this->createItems)) {
            $this->createItems = [
                ['item_id' => 0, 'qty' => 0, 'remark' => ''],
            ];
        }
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

    public function processCreate(): void
    {
        if ($this->createFromLocationId === 0) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select source warehouse.'];

            return;
        }

        if ($this->createToLocationId === 0) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Select destination kitchen.'];

            return;
        }

        $itemsToSave = [];
        foreach ($this->createItems as $item) {
            if ($item['item_id'] > 0 && $item['qty'] > 0) {
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
            StockMovementService::createMovement(
                $this->createFromLocationId,
                $this->createToLocationId,
                $itemsToSave,
                $this->createRemark ?: null
            );

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Movement created successfully.'];
            $this->closeOverlay();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getCreateFromLocations(): array
    {
        $locations = \App\Models\Holdings\Resto\Master\Rst_MasterLokasi::where('is_active', true)
            ->where('type', 'warehouse')
            ->orderBy('name')
            ->get()
            ->map(fn ($loc) => ['id' => $loc->id, 'name' => $loc->name])
            ->toArray();

        return $locations;
    }

    public function getCreateToLocations(): array
    {
        $locations = \App\Models\Holdings\Resto\Master\Rst_MasterLokasi::where('is_active', true)
            ->where('type', 'kitchen')
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

        $items = \App\Models\Holdings\Resto\CoreStock\Rst_StockBalance::where('location_id', $this->createFromLocationId)
            ->where('qty_available', '>', 0)
            ->with(['item', 'uom'])
            ->get()
            ->map(fn ($balance) => [
                'id' => $balance->item_id,
                'name' => $balance->item?->name ?? '-',
                'sku' => $balance->item?->sku ?? '-',
                'uom_symbols' => $balance->uom?->symbols ?? '-',
                'available_qty' => $balance->qty_available,
            ])
            ->toArray();

        return $items;
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

    public function getReceiveItems(): array
    {
        return $this->receiveItems;
    }

    protected function filter1Options(): array
    {
        return [
            '' => '-- All Status --',
            'requested' => 'Requested',
            'approved' => 'Approved',
            'in_transit' => 'In Transit',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
        ];
    }

    protected function filter2Options(): array
    {
        return [
            '' => '-- All Types --',
            'internal_transfer' => 'Internal Transfer',
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

        return view('livewire.holdings.resto.movement.internal.movement-internal-table-2', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
