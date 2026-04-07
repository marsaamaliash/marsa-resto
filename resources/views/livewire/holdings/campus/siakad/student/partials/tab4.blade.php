<div class="space-y-4">

    {{-- Father --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block font-medium">Father's Name</label>
            <input type="text" wire:model.defer="nama_ayah" class="w-full border-gray-300 rounded p-2">
        </div>

        <div>
            <label class="block font-medium">Parent Phone</label>
            <input type="text" wire:model.defer="no_hp_parent" class="w-full border-gray-300 rounded p-2">
        </div>
    </div>

    {{-- Mother --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block font-medium">Mother's Name</label>
            <input type="text" wire:model.defer="nama_ibu" class="w-full border-gray-300 rounded p-2">
        </div>

        <div>
            <label class="block font-medium">City of Origin</label>
            <input type="text" wire:model.defer="kota_asal" class="w-full border-gray-300 rounded p-2">
        </div>
    </div>

    {{-- Province --}}
    <div>
        <label class="block font-medium">Province of Origin</label>
        <input type="text" wire:model.defer="propinsi_asal" class="w-full border-gray-300 rounded p-2">
    </div>

    {{-- Home Address --}}
    <div>
        <label class="block font-medium">Address of Origin</label>
        <textarea wire:model.defer="alamat_asal" class="w-full border-gray-300 rounded p-2"></textarea>
    </div>

</div>
