<?php

namespace App\Livewire\Holdings\Resto\Master;

use Livewire\Component;

class DashboardMaster extends Component
{
       public array $breadcrumbs = [];

        public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'route' => 'dashboard.resto', 'color' => 'text-gray-800'],
            ['label' => 'Master Data', 'color' => 'text-gray-900 font-semibold'],
        ];
    }
       
    public function render()
    {
        return view('livewire.holdings.resto.master.dashboard-master')
            ->layout('components.sccr-layout');
    }
}
