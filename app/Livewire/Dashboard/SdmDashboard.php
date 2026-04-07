<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class SdmDashboard extends Component
{
    public array $breadcrumbs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'route' => 'dashboard.hq', 'color' => 'text-gray-800'],
            ['label' => 'SDM', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.sdm-dashboard')
            ->layout('components.sccr-layout');
    }
}
