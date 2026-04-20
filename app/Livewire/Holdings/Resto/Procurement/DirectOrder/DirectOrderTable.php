<?php

namespace App\Livewire\Holdings\Resto\Procurement\DirectOrder;

use App\Models\Holdings\Resto\Master\Rst_MasterLokasi;
use App\Services\Resto\DirectOrderService;
use Livewire\Component;
use Livewire\WithPagination;

class DirectOrderTable extends Component
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
            ['label' => 'Direct Order', 'color' => 'text-gray-900 font-semibold'],
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

    public function deleteDO($id): void
    {
        try {
            $do = \App\Models\Holdings\Resto\Procurement\Rst_DirectOrder::find($id);

            if (! $do) {
                session()->flash('error', 'Direct Order tidak ditemukan');

                return;
            }

            if (! $do->canBeEdited()) {
                session()->flash('error', 'Hanya Direct Order draft atau revised yang bisa dihapus');

                return;
            }

            $do->delete();
            session()->flash('success', 'Direct Order berhasil dihapus');
        } catch (\Exception $e) {
            session()->flash('error', 'Error: '.$e->getMessage());
        }
    }

    public function render()
    {
        $dos = DirectOrderService::getDirectOrderList(
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

        return view('livewire.holdings.resto.procurement.direct-order.direct-order-table', [
            'dos' => $dos,
            'statuses' => $statuses,
        ])->layout('components.sccr-layout');
    }
}
