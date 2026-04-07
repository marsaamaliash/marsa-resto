<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class ResortDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard.resort-dashboard')
            ->layout('components.sccr-layout');

    }
}
