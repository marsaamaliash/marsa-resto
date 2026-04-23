<?php

namespace App\Livewire\Holdings\Resto\Procurement\PurchaseRequest;

use App\Models\Holdings\Resto\Procurement\Rst_PurchaseRequest;
use App\Services\Resto\PurchaseRequestService;
use Livewire\Component;

class PurchaseRequestDetail extends Component
{
    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public ?Rst_PurchaseRequest $pr = null;

    public bool $canApproveRM = false;

    public bool $canApproveSPV = false;

    public bool $canRevise = false;

    public bool $canEdit = false;

    public bool $showRejectModal = false;

    public bool $showReviseModal = false;

    public string $rejectReason = '';

    public string $reviseReason = '';

    public string $notes = '';

    public array $itemQty = [];

    public array $itemNotes = [];

    public bool $isCreator = false;

    public string $searchItem = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public function mount(int $id): void
    {
        $this->loadPR($id);
        $this->syncCaps();
    }

    private function loadPR(int $id): void
    {
        $this->pr = Rst_PurchaseRequest::with(['items.item', 'items.uom', 'requesterLocation'])->find($id);

        if (! $this->pr) {
            $this->redirectRoute('dashboard.resto.purchase-request');

            return;
        }

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Request', 'route' => 'dashboard.resto.purchase-request', 'color' => 'text-gray-800'],
            ['label' => $this->pr->pr_number ?? 'Draft PR', 'color' => 'text-gray-900 font-semibold'],
        ];

        // Initialize editable fields
        $this->notes = $this->pr->notes ?? '';
        $this->itemQty = [];
        $this->itemNotes = [];
        foreach ($this->pr->items as $item) {
            $this->itemQty[$item->id] = $item->requested_qty;
            $this->itemNotes[$item->id] = $item->notes ?? '';
        }
    }

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canApproveRM = (bool) ($u?->hasPermission('PURCHASE_REQUEST_APPROVE_RM') ?? false);
        $this->canApproveSPV = (bool) ($u?->hasPermission('PURCHASE_REQUEST_APPROVE_SPV') ?? false);
        $this->canRevise = (bool) ($u?->hasPermission('PURCHASE_REQUEST_CREATE') ?? false);
        $this->isCreator = $this->pr?->created_by === $u?->username;
        $this->canEdit = $this->pr?->canBeEdited() && ($this->canRevise || $this->isCreator);
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    public function openActionModal(string $action): void
    {
        if ($action === 'reject') {
            $this->showRejectModal = true;
            $this->rejectReason = '';
        } elseif ($action === 'revise') {
            $this->showReviseModal = true;
            $this->reviseReason = '';
        }
    }

    public function closeActionModal(): void
    {
        $this->showRejectModal = false;
        $this->showReviseModal = false;
        $this->rejectReason = '';
        $this->reviseReason = '';
    }

    public function approveByRM(): void
    {
        try {
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::approveByRM($this->pr->id, null, $user);

            $this->loadPR($this->pr->id);
            $this->closeActionModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil diapprove oleh RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function directApproveByRM(): void
    {
        try {
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::approveByRM($this->pr->id, null, $user);

            $this->loadPR($this->pr->id);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil diapprove oleh RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approveBySPV(): void
    {
        try {
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::approveBySPV($this->pr->id, null, $user);

            $this->loadPR($this->pr->id);
            $this->closeActionModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil diapprove oleh Supervisor.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function directApproveBySPV(): void
    {
        try {
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::approveBySPV($this->pr->id, null, $user);

            $this->loadPR($this->pr->id);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil diapprove oleh Supervisor.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectPR(): void
    {
        try {
            if (empty($this->rejectReason)) {
                throw new \Exception('Alasan reject wajib diisi.');
            }

            $level = $this->pr->status === 'pending_rm' ? 1 : 2;
            $user = auth()->user()?->username ?? 'SYSTEM';

            PurchaseRequestService::reject($this->pr->id, $this->rejectReason, $level, $user);

            $this->loadPR($this->pr->id);
            $this->closeActionModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Purchase Request berhasil direject.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function requestRevise(): void
    {
        try {
            if (empty($this->reviseReason)) {
                throw new \Exception('Alasan revise wajib diisi.');
            }

            $level = $this->pr->status === 'pending_rm' ? 1 : 2;
            $user = auth()->user()?->username ?? 'SYSTEM';

            PurchaseRequestService::requestRevise($this->pr->id, $this->reviseReason, $level, $user);

            $this->loadPR($this->pr->id);
            $this->closeActionModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Request revise berhasil dikirim ke Store Keeper.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function back(): void
    {
        $this->redirectRoute('dashboard.resto.purchase-request');
    }

    public function updateNotes(): void
    {
        if (! $this->pr->canBeEdited()) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'PR tidak dapat diedit pada status ini.'];

            return;
        }

        try {
            $this->pr->notes = $this->notes;
            $this->pr->updated_by = auth()->user()?->username;
            $this->pr->save();

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Catatan PR berhasil diupdate.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function updateItems(): void
    {
        if (! $this->pr->canBeEdited()) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'PR tidak dapat diedit pada status ini.'];

            return;
        }

        try {
            foreach ($this->pr->items as $item) {
                if (isset($this->itemQty[$item->id])) {
                    $qty = (float) $this->itemQty[$item->id];
                    if ($qty > 0) {
                        $item->requested_qty = $qty;
                    }
                }
                if (array_key_exists($item->id, $this->itemNotes)) {
                    $item->notes = $this->itemNotes[$item->id] ?: null;
                }
                $item->save();
            }

            PurchaseRequestService::recalculateTotalCost($this->pr);
            $this->loadPR($this->pr->id);

            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Item berhasil diupdate.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function sortItems(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function submitForApproval(): void
    {
        try {
            $user = auth()->user()?->username ?? 'SYSTEM';
            PurchaseRequestService::submitToRM($this->pr->id, $this->notes, $user);

            $this->loadPR($this->pr->id);
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'PR berhasil disubmit ke RM untuk approval.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getFilteredItemsProperty()
    {
        $items = $this->pr?->items ?? collect();

        if ($this->searchItem !== '') {
            $search = strtolower($this->searchItem);
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->item?->name ?? ''), $search)
                    || str_contains(strtolower($item->item?->sku ?? ''), $search)
                    || str_contains(strtolower($item->notes ?? ''), $search);
            });
        }

        $sortBy = $this->sortBy;
        $direction = $this->sortDirection;

        return $items->sortBy(function ($item) use ($sortBy) {
            return match ($sortBy) {
                'sku' => strtolower($item->item?->sku ?? ''),
                'qty' => $item->requested_qty,
                'actual_stock' => $item->actual_stock,
                'min_stock' => $item->min_stock,
                default => strtolower($item->item?->name ?? ''),
            };
        }, SORT_REGULAR, $direction === 'desc')->values();
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.purchase-request.purchase-request-detail');
    }
}
