<?php

namespace App\Livewire\Holdings\Resto\Procurement\PurchaseOrder;

use Livewire\Component;

class DashboardPurchaseOrder extends Component
{
    public array $breadcrumbs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'route' => 'dashboard.resto.procurement', 'color' => 'text-gray-800'],
            ['label' => 'Purchase Order (PO)', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        return view('livewire.holdings.resto.procurement.purchase-order.dashboard-purchase-order')
            ->layout('components.sccr-layout');
    }
}
