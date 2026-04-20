<?php

namespace App\Livewire\Holdings\Resto\Master\Vendor;

use App\Models\Holdings\Resto\Master\Rst_MasterVendor;
use Illuminate\Validation\Rule;
use Livewire\Component;

class VendorEdit extends Component
{
    public Rst_MasterVendor $vendor;

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

    public function mount(string $id): void
    {
        $this->vendor = Rst_MasterVendor::withTrashed()->findOrFail($id);

        $this->name = $this->vendor->name;
        $this->code = $this->vendor->code;
        $this->email = $this->vendor->email ?? '';
        $this->pic = $this->vendor->pic ?? '';
        $this->no_telp = $this->vendor->no_telp ?? '';
        $this->address = $this->vendor->address ?? '';
        $this->description = $this->vendor->description ?? '';
        $this->default_terms = $this->vendor->default_terms;
        $this->is_active = (bool) $this->vendor->is_active;
    }

    public function update(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sccr_resto.vendors', 'name')->ignore($this->vendor->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('sccr_resto.vendors', 'code')->ignore($this->vendor->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('sccr_resto.vendors', 'email')->ignore($this->vendor->id)],
            'pic' => ['nullable', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['nullable', 'string', 'max:65535'],
            'description' => ['nullable', 'string', 'max:65535'],
            'default_terms' => ['nullable', 'in:cash,7_hari,30_hari'],
        ]);

        $this->vendor->update([
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'pic' => $this->pic,
            'no_telp' => $this->no_telp,
            'address' => $this->address,
            'description' => $this->description,
            'default_terms' => $this->default_terms,
            'is_active' => $this->is_active,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Vendor berhasil diperbarui'];

        $this->dispatch('vendor-updated');
        $this->dispatch('vendor-overlay-close');
    }

    public function saveDraft(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sccr_resto.vendors', 'name')->ignore($this->vendor->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('sccr_resto.vendors', 'code')->ignore($this->vendor->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('sccr_resto.vendors', 'email')->ignore($this->vendor->id)],
            'pic' => ['nullable', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:50', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['nullable', 'string', 'max:65535'],
            'description' => ['nullable', 'string', 'max:65535'],
            'default_terms' => ['nullable', 'in:cash,7_hari,30_hari'],
        ]);

        $this->vendor->update([
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'pic' => $this->pic,
            'no_telp' => $this->no_telp,
            'address' => $this->address,
            'description' => $this->description,
            'default_terms' => $this->default_terms,
            'is_active' => false,
        ]);

        $this->toast = ['show' => true, 'type' => 'success', 'message' => 'Draft vendor berhasil disimpan'];

        $this->dispatch('vendor-updated');
        $this->dispatch('vendor-overlay-close');
    }

    public function cancel(): void
    {
        $this->dispatch('close-overlay');
    }

    public function render()
    {
        return view('livewire.holdings.resto.master.vendor.vendor-edit');
    }
}
