<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class HqDashboard extends Component
{
    public array $breadcrumbs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Holding HQ', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.hq-dashboard')
            ->layout('components.sccr-layout');

    }
}
