<?php

namespace App\Livewire\Holdings\Resto\Movement\Internal;

use App\Models\Holdings\Resto\CoreStock\Rst_StockBalance;
use App\Models\Holdings\Resto\Movement\Rst_Movement;
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
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Master Movement', 'route' => 'dashboard.resto.master-movement', 'color' => 'text-gray-900 font-semibold'],
            ['label' => 'Movement Internal', 'color' => 'text-gray-900 font-semibold'],
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
                $q->whereHas('fromLocation', fn ($lq) => $lq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('toLocation', fn ($lq) => $lq->where('name', 'like', "%{$search}%"))
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%")
                    ->orWhereHas('items.item', fn ($iq) => $iq->where('name', 'like', "%{$search}%"));
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

        $ws->fromArray([['ID', 'From Location', 'To Location', 'Status', 'PIC', 'Remark', 'Created At']], null, 'A1');

        $row = 2;
        foreach ($data as $item) {
            $ws->fromArray([
                $item['id'] ?? '',
                $item->fromLocation?->name ?? '',
                $item->toLocation?->name ?? '',
                $item['status'] ?? '',
                $item['pic_name'] ?? '',
                $item['remark'] ?? '',
                $item['created_at'] ?? '',
            ], null, 'A'.$row++);
        }

        $filename = "MovementInternal_{$type}_".now()->format('Ymd_His').'.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tmp = tempnam(sys_get_temp_dir(), 'movement_');
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

    #[On('movement-internal-overlay-close')]
    public function handleOverlayClose(): void
    {
        $this->closeOverlay();
    }

    #[On('movement-internal-created')]
    public function handleCreated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data berhasil ditambahkan.'];
    }

    #[On('movement-internal-updated')]
    public function handleUpdated(?string $id = null): void
    {
        $this->closeOverlay();
        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Data berhasil diperbarui.'];
    }

    #[On('movement-internal-open-edit')]
    public function handleOpenEditFromShow(string $id): void
    {
        $this->openEdit($id);
    }

    public function excChefCanEdit(string $id): void
    {
        $movement = Rst_Movement::with(['items.item', 'items.uom'])->find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa edit pada status Requested.'];

            return;
        }

        $this->reviseItems = $movement->items->map(fn ($item) => [
            'movement_item_id' => $item->id,
            'item_id' => $item->item_id,
            'item_name' => $item->item?->name ?? '-',
            'qty' => $item->qty,
            'qty_original' => $item->qty,
            'qty_temp' => $item->qty,
            'uom_symbols' => $item->uom?->symbols ?? '',
            'is_removed' => false,
        ])->toArray();

        $this->reviseItemToAdd = 0;
        $this->reviseQtyToAdd = 0;
        $this->loadAvailableItemsForRevise($movement->from_location_id);

        $this->overlayMode = 'edit-revise';
        $this->overlayId = $id;
    }

    public array $reviseItems = [];

    public int $reviseItemToAdd = 0;

    public float $reviseQtyToAdd = 0;

    public array $availableItemsForRevise = [];

    private function loadAvailableItemsForRevise(int $locationId): void
    {
        $existingItemIds = collect($this->reviseItems)->pluck('item_id')->toArray();

        $this->availableItemsForRevise = Rst_StockBalance::where('location_id', $locationId)
            ->where('qty_available', '>', 0)
            ->with(['item', 'item.uom'])
            ->get()
            ->filter(fn ($b) => ! in_array($b->item_id, $existingItemIds))
            ->map(fn ($b) => [
                'id' => $b->item_id,
                'name' => $b->item?->name ?? '-',
                'available' => $b->qty_available,
                'uom_symbols' => $b->item?->uom?->symbols ?? '',
            ])
            ->toArray();
    }

    public function addItemToRevise(): void
    {
        if (! $this->overlayId || ! $this->reviseItemToAdd) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Pilih item terlebih dahulu.'];

            return;
        }

        if (! $this->reviseQtyToAdd || $this->reviseQtyToAdd <= 0) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Masukkan qty yang diinginkan.'];

            return;
        }

        $selectedItem = collect($this->availableItemsForRevise)->firstWhere('id', $this->reviseItemToAdd);
        if (! $selectedItem) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Item tidak valid.'];

            return;
        }

        if ($this->reviseQtyToAdd > $selectedItem['available']) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Qty tidak boleh melebihi stok tersedia.'];

            return;
        }

        try {
            StockMovementService::addItemToMovement(
                (int) $this->overlayId,
                (int) $this->reviseItemToAdd,
                (float) $this->reviseQtyToAdd,
                'Added via revise'
            );

            $this->reviseItems[] = [
                'movement_item_id' => null,
                'item_id' => $selectedItem['id'],
                'item_name' => $selectedItem['name'],
                'qty' => $this->reviseQtyToAdd,
                'qty_original' => 0,
                'qty_temp' => $this->reviseQtyToAdd,
                'uom_symbols' => $selectedItem['uom_symbols'],
                'is_removed' => false,
            ];

            $this->reviseItemToAdd = 0;
            $this->reviseQtyToAdd = 0;
            $this->loadAvailableItemsForRevise(Rst_Movement::find($this->overlayId)->from_location_id);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Item berhasil ditambahkan.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function removeItemFromRevise(int $index): void
    {
        if (! isset($this->reviseItems[$index])) {
            return;
        }

        $item = $this->reviseItems[$index];

        if ($item['qty_original'] > 0) {
            try {
                StockMovementService::removeItemFromMovement(
                    (int) $this->overlayId,
                    (int) $item['movement_item_id'],
                    'Removed via revise'
                );
            } catch (\Exception $e) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];

                return;
            }
        }

        $this->reviseItems[$index]['is_removed'] = true;
    }

    public function excChefSaveRevise(): void
    {
        if (! $this->overlayId || empty($this->reviseItems)) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak valid.'];

            return;
        }

        try {
            $changes = [];

            foreach ($this->reviseItems as $item) {
                if ($item['is_removed']) {
                    continue;
                }

                $oldQty = $item['qty_original'];
                $newQty = $item['qty_temp'];

                if ($oldQty != $newQty && $oldQty > 0) {
                    StockMovementService::reviseMovement(
                        (int) $this->overlayId,
                        (int) $item['item_id'],
                        (float) $newQty,
                        "Revised from {$oldQty} to {$newQty}"
                    );

                    $changes[] = "{$item['item_name']}: {$oldQty} → {$newQty}";
                }
            }

            // if (empty($changes)) {
            //     $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Tidak ada perubahan qty.'];

            //     return;
            // }

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Item(s) berhasil direvisi: '.implode(', ', $changes)];
            $this->closeOverlay();
            $this->reviseItems = [];
            $this->availableItemsForRevise = [];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function excChefCanApprove(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 0) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Exc Chef sudah approve. Hubungi RM.'];

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
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 1) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Belum di-approve oleh Exc Chef.'];

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
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa approve pada status Requested.'];

            return;
        }

        if (($movement->approval_level ?? 0) !== 2) {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Belum di-approve oleh RM.'];

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
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'requested') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa reject pada status Requested.'];

            return;
        }

        try {
            $rejecterName = auth()->user()?->name ?? 'Exc Chef';
            StockMovementService::rejectMovement((int) $id, $rejecterName, 'Rejected by Exc Chef');
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Movement rejected, stock dikembalikan.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function storeKeeperDispatch(string $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'approved') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa dispatch pada status Approved.'];

            return;
        }

        try {
            StockMovementService::dispatchItems((int) $id, 'Dispatched by Store Keeper');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Items dispatched.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function openReceiveOverlay(string $id): void
    {
        $movement = Rst_Movement::with(['items.item', 'items.uom', 'fromLocation', 'toLocation'])->find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'in_transit') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa terima pada status In Transit.'];

            return;
        }

        $this->receiveOverlayMode = 'receive';
        $this->receiveOverlayId = $id;
        $this->receiveNotes = '';
    }

    public function closeReceiveOverlay(): void
    {
        $this->receiveOverlayMode = null;
        $this->receiveOverlayId = null;
        $this->receiveNotes = '';
    }

    public function confirmReceiveComplete(int $id): void
    {
        $movement = Rst_Movement::find($id);
        if (! $movement) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Data tidak ditemukan.'];

            return;
        }

        if ($movement->status !== 'in_transit') {
            $this->toast = ['show' => true, 'type' => 'warning', 'message' => 'Hanya bisa terima pada status In Transit.'];

            return;
        }

        try {
            StockMovementService::receiveItems($id, $this->receiveNotes ?: 'Received by Dapur');
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Barang diterima.'];
            $this->closeReceiveOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function filter1Options(): array
    {
        return [
            '' => '-- Semua Status --',
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
            '' => '-- Semua Tipe --',
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

        return view('livewire.holdings.resto.movement.internal.movement-internal-table', [
            'data' => $data,
            'breadcrumbs' => $this->breadcrumbs,
            'filter1Options' => $this->filter1Options(),
            'filter2Options' => $this->filter2Options(),
        ])->layout('components.sccr-layout');
    }
}
