<?php

namespace App\Livewire;

use Livewire\Component;

class SccrToolbar extends Component
{
    public function render()
    {
        return view('livewire.sccr-toolbar')->layout('components.sccr-layout');
    }
}
