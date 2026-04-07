<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class SiakadDashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard.siakad-dashboard')
            ->layout('components.sccr-layout');
    }
}
