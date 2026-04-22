<?php

namespace App\Livewire\Holdings\Resto\Procurement\GoodsReceipt;

use App\Models\Holdings\Resto\Procurement\Rst_GoodsReceipt;
use App\Services\Resto\GoodsReceiptService;
use Livewire\Component;
use Livewire\WithFileUploads;

class GoodsReceiptDetail extends Component
{
    use WithFileUploads;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public ?int $grId = null;

    public ?Rst_GoodsReceipt $gr = null;

    public bool $showApprovalModal = false;

    public bool $showRejectModal = false;

    public string $approvalNotes = '';

    public string $rejectReason = '';

    public string $mode = 'view';

    public function mount(int $id): void
    {
        $this->grId = $id;
        $this->loadGR();

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Goods Receipt', 'route' => 'dashboard.resto.goods-receipt', 'color' => 'text-gray-800'],
            ['label' => $this->gr?->receipt_number ?? 'Detail', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function loadGR(): void
    {
        $this->gr = Rst_GoodsReceipt::with([
            'purchaseOrder.vendor',
            'purchaseOrder.location',
            'location',
            'receivedBy',
            'items.item',
            'items.purchaseOrderItem.uom',
        ])->find($this->grId);
    }

    public function isRMApprover(): bool
    {
        return (bool) (auth()->user()?->hasPermission('GOODS_RECEIPT_APPROVE_RM') ?? false);
    }

    public function isSPVApprover(): bool
    {
        return (bool) (auth()->user()?->hasPermission('GOODS_RECEIPT_APPROVE_SPV') ?? false);
    }

    public function canEdit(): bool
    {
        return $this->gr && $this->gr->canBeEdited();
    }

    public function submitForApproval(): void
    {
        try {
            GoodsReceiptService::submitForApproval($this->grId);
            $this->loadGR();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil disubmit ke Restaurant Manager.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approveByRM(): void
    {
        try {
            GoodsReceiptService::approveByRM($this->grId, $this->approvalNotes);
            $this->loadGR();
            $this->showApprovalModal = false;
            $this->approvalNotes = '';
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil diapprove oleh RM.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function approveBySPV(): void
    {
        try {
            GoodsReceiptService::approveBySPV($this->grId, $this->approvalNotes);
            $this->loadGR();
            $this->showApprovalModal = false;
            $this->approvalNotes = '';
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil diapprove oleh Supervisor. Stok telah diperbarui.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function rejectGR(): void
    {
        try {
            if (empty($this->rejectReason)) {
                $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Alasan reject wajib diisi.'];

                return;
            }

            GoodsReceiptService::reject($this->grId, $this->rejectReason);
            $this->loadGR();
            $this->showRejectModal = false;
            $this->rejectReason = '';
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Goods Receipt berhasil direject.'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.goods-receipt.goods-receipt-detail')
            ->layout('components.sccr-layout');
    }
}
