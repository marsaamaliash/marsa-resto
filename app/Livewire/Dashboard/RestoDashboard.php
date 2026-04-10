<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class RestoDashboard extends Component
{
    public array $breadcrumbs = [];

    public function mount(): void
    {
        $this->breadcrumbs = [
            ['label' => 'Main Dashboard', 'route' => 'dashboard', 'color' => 'text-gray-800'],
            ['label' => 'Resto', 'color' => 'text-gray-900 font-semibold'],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.resto-dashboard')
            ->layout('components.sccr-layout');
    }
}
