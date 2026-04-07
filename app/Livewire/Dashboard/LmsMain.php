<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class LmsMain extends Component
{
    public function render()
    {
        return view('livewire.dashboard.lms-main')
            ->layout('components.sccr-layout');
    }
}
