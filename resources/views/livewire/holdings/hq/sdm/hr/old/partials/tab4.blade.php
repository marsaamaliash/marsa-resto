<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <x-sccr-input name="no_rekening" label="No Rekening" wire:model="no_rekening" type="text" />

    <x-sccr-input name="pemilik_rekening" label="Pemilik Rekening" wire:model="pemilik_rekening" type="text" />

    <x-sccr-input name="nama_bank" label="Nama Bank" wire:model="nama_bank" type="text" />

    {{-- Salary (Disabled) --}}
    {{-- <x-sccr-input name="salary" label="Salary" wire:model="salary" type="text" disabled /> --}}

    {{-- Tunjangan (Disabled) --}}
    {{-- <x-sccr-input name="tunjangan" label="Tunjangan" wire:model="tunjangan" type="text" disabled /> --}}
</div>
