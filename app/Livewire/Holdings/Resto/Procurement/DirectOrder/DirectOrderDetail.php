<?php

namespace App\Livewire\Holdings\Resto\Procurement\DirectOrder;

use App\Models\Holdings\Resto\Procurement\Rst_DirectOrder;
use App\Services\Resto\DirectOrderService;
use Livewire\Component;
use Livewire\WithFileUploads;

class DirectOrderDetail extends Component
{
    use WithFileUploads;

    public array $breadcrumbs = [];

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public ?Rst_DirectOrder $do = null;

    public ?int $doId = null;

    public bool $showRejectModal = false;

    public bool $showReviseModal = false;

    public string $rejectReason = '';

    public string $reviseReason = '';

    public $newProofFile = null;

    public array $itemPrices = [];

    public string $paymentBy = 'holding';

    public string $doNotes = '';

    public function mount(?int $id = null): void
    {
        $this->doId = $id;
        if ($this->doId) {
            $this->loadDO();
        }
    }

    public function loadDO(): void
    {
        $this->do = Rst_DirectOrder::with(['location', 'items.item', 'items.uom'])->find($this->doId);

        if (! $this->do) {
            redirect()->route('dashboard.resto.direct-order');
        }

        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Direct Order', 'route' => 'dashboard.resto.direct-order', 'color' => 'text-gray-800'],
            ['label' => $this->do->do_number, 'color' => 'text-gray-900 font-semibold'],
        ];

        if ($this->do->canBeEdited()) {
            $this->itemPrices = [];
            foreach ($this->do->items as $item) {
                $this->itemPrices[$item->id] = $item->unit_price;
            }

            $this->paymentBy = $this->do->payment_by ?? 'holding';
            $this->doNotes = $this->do->notes ?? '';
        }
    }

    public function submitForApproval(): void
    {
        try {
            DirectOrderService::submitForApproval($this->doId);
            $this->loadDO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Direct Order berhasil disubmit untuk approval RM'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function approveByRM(): void
    {
        try {
            DirectOrderService::approveByRM($this->doId);
            $this->loadDO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Direct Order berhasil diapprove RM, menunggu approval Supervisor'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function approveBySPV(): void
    {
        try {
            DirectOrderService::approveBySPV($this->doId);
            $this->loadDO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Direct Order berhasil diapprove Supervisor. Sudah siap'];
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

    public function rejectDO(): void
    {
        $this->validate([
            'rejectReason' => 'required|string',
        ]);

        try {
            DirectOrderService::reject($this->doId, $this->rejectReason);
            $this->loadDO();
            $this->closeRejectModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Direct Order berhasil ditolak'];
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
            DirectOrderService::requestRevision($this->doId, $this->reviseReason);
            $this->loadDO();
            $this->closeReviseModal();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Permintaan revisi berhasil. Direct Order kembali ke status draft'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function updateProof(): void
    {
        $this->validate([
            'newProofFile' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        try {
            DirectOrderService::updateProof($this->doId, $this->newProofFile);
            $this->loadDO();
            $this->newProofFile = null;
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Bukti berhasil diupdate'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function updateItemPrices(): void
    {
        if (! $this->do->canBeEdited()) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Direct Order tidak dapat diedit pada status ini'];

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
                DirectOrderService::updateItemPricing($itemId, (float) $price, $this->do->items->find($itemId)->quantity);
            }

            $this->loadDO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Harga item berhasil diupdate'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function updateDODetails(): void
    {
        if (! $this->do->canBeEdited()) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Direct Order tidak dapat diedit pada status ini'];

            return;
        }

        $this->validate([
            'paymentBy' => 'required|string',
            'doNotes' => 'nullable|string',
        ]);

        try {
            DirectOrderService::updateDODetails(
                $this->doId,
                0,
                $this->paymentBy,
                $this->doNotes ?: null
            );

            $this->loadDO();
            $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Detail Direct Order berhasil diupdate'];
        } catch (\Exception $e) {
            $this->toast = ['show' => true, 'type' => 'error', 'message' => 'Error: '.$e->getMessage()];
        }
    }

    public function isRMApprover(): bool
    {
        return auth()->user()?->hasPermission('do_rm_approval') ?? false;
    }

    public function isSPVApprover(): bool
    {
        return auth()->user()?->hasPermission('do_spv_approval') ?? false;
    }

    public function isCreator(): bool
    {
        return $this->do?->created_by === auth()->user()?->username;
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.direct-order.direct-order-detail', [
            'isCreator' => $this->isCreator(),
            'isRMApprover' => $this->isRMApprover(),
            'isSPVApprover' => $this->isSPVApprover(),
        ])->layout('components.sccr-layout');
    }
}
