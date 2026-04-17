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

    public ?string $actionModal = null;

    public string $actionNotes = '';

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
            ['label' => $this->pr->pr_number, 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    private function syncCaps(): void
    {
        $u = auth()->user();

        $this->canApproveRM = (bool) ($u?->hasPermission('PURCHASE_REQUEST_APPROVE_RM') ?? false);
        $this->canApproveSPV = (bool) ($u?->hasPermission('PURCHASE_REQUEST_APPROVE_SPV') ?? false);
        $this->canRevise = (bool) ($u?->hasPermission('PURCHASE_REQUEST_CREATE') ?? false);
        $this->canEdit = $this->pr?->canBeEdited() && $this->canRevise;
    }

    public function hydrate(): void
    {
        $this->syncCaps();
    }

    public function openActionModal(string $action): void
    {
        $this->actionModal = $action;
        $this->actionNotes = '';
    }

    public function closeActionModal(): void
    {
        $this->reset('actionModal', 'actionNotes');
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
            if (empty($this->actionNotes)) {
                throw new \Exception('Alasan reject wajib diisi.');
            }

            $level = $this->pr->status === 'pending_rm' ? 1 : 2;
            $user = auth()->user()?->username ?? 'SYSTEM';

            PurchaseRequestService::reject($this->pr->id, $this->actionNotes, $level, $user);

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
            if (empty($this->actionNotes)) {
                throw new \Exception('Alasan revise wajib diisi.');
            }

            $level = $this->pr->status === 'pending_rm' ? 1 : 2;
            $user = auth()->user()?->username ?? 'SYSTEM';

            PurchaseRequestService::requestRevise($this->pr->id, $this->actionNotes, $level, $user);

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

    public function edit(): void
    {
        $this->redirectRoute('dashboard.resto.purchase-request.create', ['id' => $this->pr->id]);
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.purchase-request.purchase-request-detail');
    }
}
