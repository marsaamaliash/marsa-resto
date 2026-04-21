<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Edit Location</h2>

    <form wire:submit.prevent="update" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location Name <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="name"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. Main Warehouse">
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                <input type="text" wire:model.defer="code"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. WH-01">
                @error('code')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                <select wire:model.defer="type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select --</option>
                    <option value="warehouse" {{ $type === 'warehouse' ? 'selected' : '' }}>Warehouse</option>
                    <option value="kitchen" {{ $type === 'kitchen' ? 'selected' : '' }}>Kitchen</option>
                    <option value="outlet" {{ $type === 'outlet' ? 'selected' : '' }}>Outlet</option>
                    <option value="transit" {{ $type === 'transit' ? 'selected' : '' }}>Transit</option>
                </select>
                @error('type')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="pic_name"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. Budi Santoso">
                @error('pic_name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model.defer="notes"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Notes tambahan (Optional)" rows="2"></textarea>
            @error('notes')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model.defer="is_active" id="edit_is_active"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="edit_is_active" class="text-sm text-gray-700">Active</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Save
            </button>
            <button type="button" wire:click="cancel"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancel
            </button>
        </div>
    </form>
</div>
