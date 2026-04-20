<?php

namespace App\Livewire\Holdings\Resto\Procurement\PurchaseOrder;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Services\Resto\PurchaseOrderService;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderTable extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public int $selectedLocationId = 0;

    public array $locations = [];

    public array $breadcrumbs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Order', 'color' => 'text-gray-900 font-semibold'],
        ];

        $this->locations = Rst_MasterLokasi::where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();

        if (! empty($this->locations)) {
            $this->selectedLocationId = array_key_first($this->locations);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedLocationId(): void
    {
        $this->resetPage();
    }

    public function deletePO($id): void
    {
        try {
            $po = \App\Models\Holdings\Resto\Procurement\Rst_PurchaseOrder::find($id);

            if (! $po) {
                session()->flash('error', 'PO tidak ditemukan');

                return;
            }

            if (! $po->canBeEdited()) {
                session()->flash('error', 'Hanya PO draft atau revised yang bisa dihapus');

                return;
            }

            $po->delete();
            session()->flash('success', 'PO berhasil dihapus');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: '.$e->getMessage());
        }
    }

    public function render()
    {
        $pos = PurchaseOrderService::getPOList(
            $this->selectedLocationId,
            $this->statusFilter ?: null,
            $this->search ?: null
        );

        $statuses = [
            'draft' => 'Draft',
            'pending_rm' => 'Pending RM',
            'pending_spv' => 'Pending SPV',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'revised' => 'Revised',
        ];

        return view('livewire.holdings.resto.procurement.purchase-order.purchase-order-table', [
            'pos' => $pos,
            'statuses' => $statuses,
        ])->layout('components.sccr-layout');
    }
}
