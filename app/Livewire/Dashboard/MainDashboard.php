<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class MainDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard.main-dashboard')
            ->layout('components.sccr-layout');
    }
}
