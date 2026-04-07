<div class="space-y-4">

    {{-- Email --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block font-medium">Private Email</label>
            <input type="email" wire:model.defer="email_private" class="w-full border-gray-300 rounded p-2">
        </div>

        <div>
            <label class="block font-medium">Campus Email</label>
            <input type="email" wire:model.defer="email_campus" class="w-full border-gray-300 rounded p-2">
        </div>
    </div>

    {{-- Phone --}}
    <div>
        <label class="block font-medium">Phone Number</label>
        <input type="text" wire:model.defer="no_hp" class="w-full border-gray-300 rounded p-2">
    </div>

    {{-- Address --}}
    <div>
        <label class="block font-medium">Current Address</label>
        <textarea wire:model.defer="alamat_domisili" class="w-full border-gray-300 rounded p-2"></textarea>
    </div>

    {{-- City --}}
    <div>
        <label class="block font-medium">City</label>
        <input type="text" wire:model.defer="kota_domisili" class="w-full border-gray-300 rounded p-2">
    </div>

</div>
