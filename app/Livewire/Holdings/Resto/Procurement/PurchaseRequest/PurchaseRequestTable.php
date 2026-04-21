<?php

namespace App\Livewire\Holdings\Resto\Procurement\PurchaseRequest;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Models\Holdings\Resto\Procurement\Rst_PurchaseRequest;
use App\Services\Resto\PurchaseRequestService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseRequestTable extends Component
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

    public int $totalAll = 0;

    protected array $allowedSortFields = [
        'id',
        'pr_number',
        'requester_location_id',
        'status',
        'approval_level',
        'requested_by',
        'requested_at',
        'total_estimated_cost',
        'created_at',
        'updated_at',
    ];

    public array $selectedItems = [];

    public bool $selectAll = false;

    public ?string $overlayMode = null;

    public ?string $overlayId = null;

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

        $this->canCreate = (bool) ($u?->hasPermission('PURCHASE_REQUEST_CREATE') ?? false);
        $this->canUpdate = (bool) ($u?->hasPermission('PURCHASE_REQUEST_UPDATE') ?? false);
        $this->canDelete = (bool) ($u?->hasPermission('PURCHASE_REQUEST_DELETE') ?? false);
        $this->canApproveRM = (bool) ($u?->hasPermission('PURCHASE_REQUEST_APPROVE_RM') ?? false);
        $this->canApproveSPV = (bool) ($u?->hasPermission('PURCHASE_REQUEST_APPROVE_SPV') ?? false);
        $this->canExport = (bool) ($u?->hasPermission('PURCHASE_REQUEST_EXPORT') ?? false);
        $this->canRevise = $this->canCreate;

        $this->canWrite = $this->canCreate || $this->canUpdate;
    }

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Request', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->syncCaps();

        $this->totalAll = Rst_PurchaseRequest::count();
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    protected function dataQuery(): Collection
    {
        $query = Rst_PurchaseRequest::with(['items.item', 'items.uom', 'requesterLocation']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('pr_number', 'like', "%{$search}%")
                    ->orWhere('requested_by', 'like', "%{$search}%")
                    ->orWhereHas('requesterLocation', fn ($lq) => $lq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('items.item', fn ($iq) => $iq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($this->filterStatus !== '') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterLocation !== '') {
            $query->where('requester_location_id', $this->filterLocation);
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

        return view('livewire.holdings.resto.procurement.purchase-request.purchase-request-table', [
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

        $this->resetPage();
    }

    public function openCreateFromCritical(): void
    {
        $this->redirectRoute('dashboard.resto.purchase-request.create');
    }

    public function openCreateBlank(): void
    {
        $this->redirectRoute('dashboard.resto.purchase-request.create');
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
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::approveByRM((int) $this->actionOverlayId, null, $user);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil diapprove oleh RM.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function directApproveByRM(int $prId): void
    {
        try {
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::approveByRM($prId, null, $user);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil diapprove oleh RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approveBySPV(): void
    {
        try {
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::approveBySPV((int) $this->actionOverlayId, null, $user);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil diapprove oleh Supervisor.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function directApproveBySPV(int $prId): void
    {
        try {
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::approveBySPV($prId, null, $user);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil diapprove oleh Supervisor.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectPR(): void
    {
        try {
            if (empty($this->actionNotes)) {
                throw new \Exception('Alasan reject wajib diisi.');
            }

            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::reject((int) $this->actionOverlayId, $this->actionNotes, $this->actionTargetLevel, $user);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil direject.'];
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

            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::requestRevise((int) $this->actionOverlayId, $this->actionNotes, $this->actionTargetLevel, $user);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Request revise berhasil dikirim ke Store Keeper.'];
            $this->closeActionOverlay();
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function submitDraftPRToRM(int $prId): void
    {
        try {
            PurchaseRequestService::submitToRM($prId);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil disubmit ke Restaurant Manager.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function deletePR(string $id): void
    {
        try {
            PurchaseRequestService::deletePR((int) $id);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil dihapus.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function exportExcel(): StreamedResponse
    {
        $data = $this->dataQuery();
        $filename = 'purchase_requests_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'PR Number',
                'Tanggal Request',
                'Lokasi',
                'Requester',
                'Status',
                'Level',
                'Total Items',
                'Total Cost',
                'Required Date',
                'Notes',
            ]);

            foreach ($data as $pr) {
                fputcsv($file, [
                    $pr->pr_number,
                    $pr->requested_at?->format('Y-m-d H:i') ?? '-',
                    $pr->requesterLocation?->name ?? '-',
                    $pr->requested_by ?? '-',
                    $pr->status,
                    $pr->approval_level,
                    $pr->items->count(),
                    number_format($pr->total_estimated_cost, 2),
                    $pr->required_date?->format('Y-m-d') ?? '-',
                    $pr->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    #[On('refresh-purchase-request-table')]
    public function refresh(): void
    {
        $this->resetPage();
    }
}
