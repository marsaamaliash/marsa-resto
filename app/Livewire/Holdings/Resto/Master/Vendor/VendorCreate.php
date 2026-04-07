<?php

namespace App\Livewire\Holdings\Resto\Master\Vendor;

use App\Models\Holdings\Resto\Master\Rst_MasterVendor;
use Livewire\Component;

class VendorCreate extends Component
{
    public string $name = '';

    public string $code = '';

    public string $no_telp = '';

    public string $address = '';

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public function store(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['unique:sccr_resto.vendors,code', 'string', 'max:50'],
            'no_telp' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
        ]);

        Rst_MasterVendor::create([
            'name' => $this->name,
            'code' => $this->code,
            'no_telp' => $this->no_telp,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Vendor berhasil ditambahkan'];

        $this->dispatch('vendor-created');
        $this->dispatch('vendor-overlay-close');

        $this->reset(['name', 'code', 'no_telp', 'address']);
        $this->is_active = true;
    }

    public function cancel(): void
    {
        $this->dispatch('close-overlay');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.vendor.vendor-create');
    }
}
