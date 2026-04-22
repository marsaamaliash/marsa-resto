<?php

namespace App\Livewire\Holdings\Resto\Procurement\GoodsReceipt;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Procurement\Rst_GoodsReceipt;
use App\Services\Resto\GoodsReceiptService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class GoodsReceiptTable extends Component
{
    use WithPagination;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public bool $canWrite = false;

    public bool $canCreate = false;

    public bool $canUpdate = false;

    public bool $canDelete = false;

    public bool $canApproveRM = false;

    public bool $canApproveSPV = false;

    public bool $canExport = false;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterLocation = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'id',
        'receipt_number',
        'location_id',
        'status',
        'approval_level',
        'received_at',
        'created_at',
        'updated_at',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $actionOverlayMode = null;

    public ?string $actionOverlayId = null;

    public string $actionNotes = '';

    public int $actionTargetLevel = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterLocation' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canCreate = (bool) ($u?->hasPermission('GOODS_RECEIPT_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('GOODS_RECEIPT_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('GOODS_RECEIPT_DELETE') ?? false);
        $this->canApproveRM = (bool) ($u?->hasPermission('GOODS_RECEIPT_APPROVE_RM') ?? false);
        $this->canApproveSPV = (bool) ($u?->hasPermission('GOODS_RECEIPT_APPROVE_SPV') ?? false);
        $this->canExport = (bool) ($u?->hasPermission('GOODS_RECEIPT_EXPORT') ?? false);

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Goods Receipt', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_GoodsReceipt::with(['purchaseOrder', 'location', 'receivedBy', 'items.item'])
            ->whereHas('purchaseOrder', fn ($q) => $q->where('status', 'approved'));

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', fn ($lq) => $lq->where('po_number', 'like', "%{$search}%")
                        ->orWhere('vendor_name', 'like', "%{$search}%"))
                    ->orWhereHas('location', fn ($lq) => $lq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterLocation !== '') {
            $query->where('location_id', $this->filterLocation);
        }

        $field = in_array($this->sortField, $this->allowedSortFields) ? $this->sortField : 'created_at';
        $direction = in_array($this->sortDirection, ['asc', 'desc']) ? $this->sortDirection : 'desc';

        return $query->orderBy($field, $direction)->get();
    }

    public function getFilterStatusOptionsProperty(): array
    {
        return [
            '' => 'All Status',
            'draft' => 'Draft',
            'pending_rm' => 'Pending RM',
            'pending_spv' => 'Pending SPV',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    public function getFilterLocationOptionsProperty(): array
    {
        $locations = Rst_MasterLokasi::orderBy('name')->get();
        $options = ['' => 'All Locations'];
        foreach ($locations as $loc) {
            $options[$loc->id] = $loc->name;
        }

        return $options;
    }

    public function render()
    {
        $allData = $this->dataQuery();
        $paginated = new LengthAwarePaginator(
            $allData->forPage($this->getPage(), $this->perPage),
            $allData->count(),
            $this->perPage,
            $this->getPage()
        );

        return view('livewire.holdings.resto.procurement.goods-receipt.goods-receipt-table', [
            'data' => $paginated,
        ])->layout('components.sccr-layout');
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedItems = $value ? $this->dataQuery()->pluck('id')->toArray() : [];
    }

    public function applyFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'filterStatus', 'filterLocation');
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openCreateFromPO(): void
    {
        $this->redirectRoute('dashboard.resto.goods-receipt.create');
    }

    public function openActionOverlay(string $mode, string $id, int $level = 0): void
    {
        $this->actionOverlayMode = $mode;
        $this->actionOverlayId = $id;
        $this->actionTargetLevel = $level;
        $this->actionNotes = '';
    }

    public function closeActionOverlay(): void
    {
        $this->reset('actionOverlayMode', 'actionOverlayId', 'actionTargetLevel', 'actionNotes');
    }

    public function approveByRM(): void
    {
        try {
            GoodsReceiptService::approveByRM((int) $this->actionOverlayId, null);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil diapprove oleh RM.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function directApproveByRM(int $grId): void
    {
        try {
            GoodsReceiptService::approveByRM($grId, null);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil diapprove oleh RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approveBySPV(): void
    {
        try {
            GoodsReceiptService::approveBySPV((int) $this->actionOverlayId, null);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil diapprove oleh Supervisor. Stok telah diperbarui.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function directApproveBySPV(int $grId): void
    {
        try {
            GoodsReceiptService::approveBySPV($grId, null);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil diapprove oleh Supervisor. Stok telah diperbarui.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectGR(): void
    {
        try {
            if (empty($this->actionNotes)) {
                throw new \Exception('Alasan reject wajib diisi.');
            }

            GoodsReceiptService::reject((int) $this->actionOverlayId, $this->actionNotes);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil direject.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitDraftGRToRM(int $grId): void
    {
        try {
            GoodsReceiptService::submitForApproval($grId);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil disubmit ke Restaurant Manager.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deleteGR(string $id): void
    {
        try {
            $gr = Rst_GoodsReceipt::find($id);

            if (! $gr) {
                throw new \Exception('Goods Receipt tidak ditemukan.');
            }

            if (! $gr->canBeEdited()) {
                throw new \Exception('Hanya Goods Receipt draft atau rejected yang bisa dihapus.');
            }

            $gr->delete();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil dihapus.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    #[\Livewire\Attributes\On('refresh-goods-receipt-table')]
    public function refresh(): void
    {
        $this->resetPage();
    }
}
