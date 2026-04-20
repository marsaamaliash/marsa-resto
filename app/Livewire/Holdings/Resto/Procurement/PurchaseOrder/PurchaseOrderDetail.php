<?php

namespace App\Livewire\Holdings\Resto\Procurement\PurchaseOrder;

use App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrder;
use App\Services\Resto\PurchaseOrderService;
use Livewire\Component;
use Livewire\WithFileUploads;

class PurchaseOrderDetail extends Component
{
    use WithFileUploads;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public ?Rst_PurchaseOrder $po = null;

    public ?int $poId = null;

    public bool $showApprovalModal = false;

    public bool $showRejectModal = false;

    public bool $showReviseModal = false;

    public string $approvalNotes = '';

    public string $rejectReason = '';

    public string $reviseReason = '';

    public $newQuotationFile = null;

    public array $itemPrices = [];

    public array $vendors = [];

    public int $selectedVendorId = 0;

    public string $paymentBy = 'holding';

    public string $poNotes = '';

    public function mount(?int $id = null): void
    {
        $this->poId = $id;
        if ($this->poId) {
            $this->loadPO();
        }

        $this->vendors = PurchaseOrderService::getActiveVendors();
    }

    public function loadPO(): void
    {
        $this->po = Rst_PurchaseOrder::with(['purchaseRequest', 'vendor', 'items.item', 'items.uom'])->find($this->poId);

        if (! $this->po) {
            redirect()->route('dashboard.resto.purchase-order');
        }

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Order', 'route' => 'dashboard.resto.purchase-order', 'color' => 'text-gray-800'],
            ['label' => $this->po->po_number, 'color' => 'text-gray-900 font-semibold'],
        ];

        if ($this->po->canBeEdited()) {
            $this->itemPrices = [];
            foreach ($this->po->items as $item) {
                $this->itemPrices[$item->id] = $item->unit_price;
            }

            $this->selectedVendorId = $this->po->vendor_id ?? 0;
            $this->paymentBy = $this->po->payment_by ?? 'holding';
            $this->poNotes = $this->po->notes ?? '';
        }
    }

    public function submitForApproval(): void
    {
        try {
            PurchaseOrderService::submitForApproval($this->poId);
            $this->loadPO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'PO berhasil disubmit untuk approval RM'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function openApprovalModal(): void
    {
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal(): void
    {
        $this->showApprovalModal = false;
        $this->approvalNotes = '';
    }

    public function approveByRM(): void
    {
        try {
            PurchaseOrderService::approveByRM($this->poId, $this->approvalNotes ?: null);
            $this->loadPO();
            $this->closeApprovalModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'PO berhasil diapprove RM, menunggu approval Supervisor'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function approveBySPV(): void
    {
        try {
            PurchaseOrderService::approveBySPV($this->poId, $this->approvalNotes ?: null);
            $this->loadPO();
            $this->closeApprovalModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'PO berhasil diapprove Supervisor. Sudah siap untuk pembelian'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function openRejectModal(): void
    {
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectReason = '';
    }

    public function rejectPO(): void
    {
        $this->validate([
            'rejectReason' => 'required|string',
        ]);

        try {
            PurchaseOrderService::reject($this->poId, $this->rejectReason);
            $this->loadPO();
            $this->closeRejectModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'PO berhasil ditolak'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function openReviseModal(): void
    {
        $this->showReviseModal = true;
    }

    public function closeReviseModal(): void
    {
        $this->showReviseModal = false;
        $this->reviseReason = '';
    }

    public function requestRevision(): void
    {
        $this->validate([
            'reviseReason' => 'required|string',
        ]);

        try {
            PurchaseOrderService::requestRevision($this->poId, $this->reviseReason);
            $this->loadPO();
            $this->closeReviseModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Permintaan revisi berhasil. PO kembali ke status draft'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function updateQuotation(): void
    {
        $this->validate([
            'newQuotationFile' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        try {
            PurchaseOrderService::updateQuotation($this->poId, $this->newQuotationFile);
            $this->loadPO();
            $this->newQuotationFile = null;
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Quotation berhasil diupdate'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function updateItemPrices(): void
    {
        if (! $this->po->canBeEdited()) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'PO tidak dapat diedit pada status ini'];
            return;
        }

        try {
            foreach ($this->itemPrices as $itemId => $price) {
                $price = (float) ($price ?? 0);
                if ($price <= 0) {
                    $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Semua harga item harus lebih dari 0'];
                    return;
                }
            }

            foreach ($this->itemPrices as $itemId => $price) {
                PurchaseOrderService::updateItemPricing($itemId, (float) $price, $this->po->items->find($itemId)->ordered_qty);
            }

            $this->loadPO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Harga item berhasil diupdate'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function updatePODetails(): void
    {
        if (! $this->po->canBeEdited()) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'PO tidak dapat diedit pada status ini'];
            return;
        }

        $this->validate([
            'selectedVendorId' => 'required|integer|min:1',
            'paymentBy' => 'required|in:holding,resto',
            'poNotes' => 'nullable|string',
        ]);

        try {
            PurchaseOrderService::updatePODetails(
                $this->poId,
                $this->selectedVendorId,
                $this->paymentBy,
                $this->poNotes ?: null
            );

            $this->loadPO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Detail PO berhasil diupdate'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function canEdit(): bool
    {
        return $this->po?->canBeEdited() ?? false;
    }

    public function isRMApprover(): bool
    {
        return auth()->user()?->hasPermission('po_rm_approval') ?? false;
    }

    public function isSPVApprover(): bool
    {
        return auth()->user()?->hasPermission('po_spv_approval') ?? false;
    }

    public function isCreator(): bool
    {
        return $this->po?->created_by === auth()->user()?->id;
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.purchase-order.purchase-order-detail', [
            'isCreator' => $this->isCreator(),
            'isRMApprover' => $this->isRMApprover(),
            'isSPVApprover' => $this->isSPVApprover(),
        ])->layout('components.sccr-layout');
    }
}
