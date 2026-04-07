<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class CampusDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard.campus-dashboard')
            ->layout('components.sccr-layout');
    }
}
