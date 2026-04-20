<?php

namespace App\Livewire\Holdings\Resto\Master\Vendor;

use App\Models\Holdings\Resto\Master\Rst_MasterVendor;
use Illuminate\Validation\Rule;
use Livewire\Component;

class VendorCreate extends Component
{
    public string $name = '';

    public string $code = '';

    public string $email = '';

    public string $pic = '';

    public string $no_telp = '';

    public string $address = '';

    public string $description = '';

    public ?string $default_terms = null;

    public bool $is_active = true;

    public array $toast = ['show' => false, 'type' => 'success', 'message' => ''];

    public array $termsOptions = [
        ['value' => 'cash', 'label' => 'Cash'],
        ['value' => '7_hari', 'label' => '7 Hari'],
        ['value' => '30_hari', 'label' => '30 Hari'],
    ];

    public function store(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sccr_resto.vendors', 'name')],
            'code' => ['required', 'string', 'max:50', Rule::unique('sccr_resto.vendors', 'code')],
            'email' => ['required', 'email', 'max:255', Rule::unique('sccr_resto.vendors', 'email')],
            'pic' => ['nullable', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['nullable', 'string', 'max:65535'],
            'description' => ['nullable', 'string', 'max:65535'],
            'default_terms' => ['nullable', 'in:cash,7_hari,30_hari'],
        ]);

        Rst_MasterVendor::create([
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'pic' => $this->pic,
            'no_telp' => $this->no_telp,
            'address' => $this->address,
            'description' => $this->description,
            'default_terms' => $this->default_terms,
            'status' => 'requested',
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Vendor berhasil ditambahkan'];

        $this->dispatch('vendor-created');
        $this->dispatch('vendor-overlay-close');

        $this->resetForm();
    }

    public function saveDraft(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sccr_resto.vendors', 'name')],
            'code' => ['required', 'string', 'max:50', Rule::unique('sccr_resto.vendors', 'code')],
            'email' => ['required', 'email', 'max:255', Rule::unique('sccr_resto.vendors', 'email')],
            'pic' => ['nullable', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['nullable', 'string', 'max:65535'],
            'description' => ['nullable', 'string', 'max:65535'],
            'default_terms' => ['nullable', 'in:cash,7_hari,30_hari'],
        ]);

        Rst_MasterVendor::create([
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'pic' => $this->pic,
            'no_telp' => $this->no_telp,
            'address' => $this->address,
            'description' => $this->description,
            'default_terms' => $this->default_terms,
            'status' => 'requested',
            'is_active' => false,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Draft vendor berhasil disimpan'];

        $this->dispatch('vendor-created');
        $this->dispatch('vendor-overlay-close');

        $this->resetForm();
    }

    public function cancel(): void
    {
        $this->dispatch('close-overlay');
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'code', 'email', 'pic', 'no_telp', 'address', 'description', 'default_terms']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.vendor.vendor-create');
    }
}
