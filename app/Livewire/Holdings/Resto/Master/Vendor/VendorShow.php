<?php

namespace App\Livewire\Holdings\Resto\Master\Vendor;

use App\Models\Holdings\Resto\Master\Rst_MasterVendor;
use Livewire\Component;

class VendorShow extends Component
{
    public ?Rst_MasterVendor $vendor = null;

    public function mount(string $id): void
    {
        $this->vendor = Rst_MasterVendor::withTrashed()->find($id);
    }

    public function edit(): void
    {
        $this->dispatch('vendor-overlay-close');
        $this->dispatch('vendor-open-edit', id: $this->vendor->id);
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.vendor.vendor-show');
    }
}
