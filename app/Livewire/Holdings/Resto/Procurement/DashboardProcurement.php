<?php

namespace App\Livewire\Holdings\Resto\Procurement;

use Livewire\Component;

class DashboardProcurement extends Component
{
       public array $breadcrumbs = [];

        public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Procurement', 'color' => 'text-gray-900 font-semibold'],
        ];
    }
       
    public function render()
    {
        return view('livewire.holdings.resto.procurement.dashboard-procurement')
            ->layout('components.sccr-layout');
    }
}
