<?php

namespace App\Livewire;

use Livewire\Component;

class GlobalToast extends Component
{
    public $show = false;

    public $type = 'success';

    public $message = '';

    protected $listeners = ['toast' => 'showToast'];

    public function showToast($message, $type = 'success')
    {
        $this->message = $message;
        $this->type = $type;
        $this->show = true;

        $this->dispatchBrowserEvent('toast-auto-hide');
    }

    public function render()
    {
        return view('livewire.global-toast');
    }
}
