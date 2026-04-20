<?php

namespace App\Livewire\Holdings\Resto\Procurement\PurchaseOrder;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrder;
use App\Services\Resto\PurchaseOrderService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseOrderTable extends Component
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

    public bool $canRevise = false;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterLocation = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    protected array $allowedSortFields = [
        'id',
        'po_number',
        'location_id',
        'status',
        'approval_level',
        'vendor_name',
        'total_amount',
        'payment_by',
        'created_at',
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

        $this->canCreate = (bool) ($u?->hasPermission('PURCHASE_ORDER_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('PURCHASE_ORDER_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('PURCHASE_ORDER_DELETE') ?? false);
        $this->canApproveRM = (bool) ($u?->hasPermission('PURCHASE_ORDER_APPROVE_RM') ?? false);
        $this->canApproveSPV = (bool) ($u?->hasPermission('PURCHASE_ORDER_APPROVE_SPV') ?? false);
        $this->canExport = (bool) ($u?->hasPermission('PURCHASE_ORDER_EXPORT') ?? false);
        $this->canRevise = $this->canCreate;

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Order', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_PurchaseOrder::with(['items.item', 'items.uom', 'purchaseRequest', 'vendor', 'location']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%")
                    ->orWhereHas('purchaseRequest', fn ($lq) => $lq->where('pr_number', 'like', "%{$search}%"))
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
            'revised' => 'Revised',
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

        return view('livewire.holdings.resto.procurement.purchase-order.purchase-order-table', [
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

    public function openCreateFromCritical(): void
    {
        $this->redirectRoute('dashboard.resto.purchase-order.create');
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
            PurchaseOrderService::approveByRM((int) $this->actionOverlayId, null);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Order berhasil diapprove oleh RM.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function directApproveByRM(int $poId): void
    {
        try {
            PurchaseOrderService::approveByRM($poId, null);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Order berhasil diapprove oleh RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approveBySPV(): void
    {
        try {
            PurchaseOrderService::approveBySPV((int) $this->actionOverlayId, null);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Order berhasil diapprove oleh Supervisor.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function directApproveBySPV(int $poId): void
    {
        try {
            PurchaseOrderService::approveBySPV($poId, null);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Order berhasil diapprove oleh Supervisor.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectPO(): void
    {
        try {
            if (empty($this->actionNotes)) {
                throw new \Exception('Alasan reject wajib diisi.');
            }

            PurchaseOrderService::reject((int) $this->actionOverlayId, $this->actionNotes);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Order berhasil direject.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function requestRevise(): void
    {
        try {
            if (empty($this->actionNotes)) {
                throw new \Exception('Alasan revise wajib diisi.');
            }

            PurchaseOrderService::requestRevision((int) $this->actionOverlayId, $this->actionNotes);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Request revise berhasil dikirim.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitDraftPOToRM(int $poId): void
    {
        try {
            PurchaseOrderService::submitForApproval($poId);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Order berhasil disubmit ke Restaurant Manager.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletePO(string $id): void
    {
        try {
            $po = Rst_PurchaseOrder::find($id);

            if (! $po) {
                throw new \Exception('PO tidak ditemukan.');
            }

            if (! $po->canBeEdited()) {
                throw new \Exception('Hanya PO draft atau revised yang bisa dihapus.');
            }

            $po->delete();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Order berhasil dihapus.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function exportExcel(): StreamedResponse
    {
        $data = $this->dataQuery();
        $filename = 'purchase_orders_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'PO Number',
                'PR Number',
                'Vendor',
                'Lokasi',
                'Status',
                'Total Amount',
                'Payment By',
                'Created At',
            ]);

            foreach ($data as $po) {
                fputcsv($file, [
                    $po->po_number,
                    $po->purchaseRequest?->pr_number ?? '-',
                    $po->vendor_name,
                    $po->location?->name ?? '-',
                    $po->status,
                    number_format($po->total_amount ?? 0, 2),
                    $po->payment_by,
                    $po->created_at?->format('Y-m-d H:i') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    #[\Livewire\Attributes\On('refresh-purchase-order-table')]
    public function refresh(): void
    {
        $this->resetPage();
    }
}
