<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Edit Table</h2>

    <form wire:submit.prevent="update" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Table No. <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="table_number"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. 01">
                @error('table_number')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity <span class="text-red-500">*</span></label>
                <input type="number" wire:model.defer="capacity" min="1" max="50"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. 4">
                @error('capacity')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Area <span class="text-red-500">*</span></label>
                <select wire:model.defer="area" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Area --</option>
                    <option value="indoor" {{ $area === 'indoor' ? 'selected' : '' }}>Indoor</option>
                    <option value="outdoor" {{ $area === 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                    <option value="vip" {{ $area === 'vip' ? 'selected' : '' }}>VIP</option>
                    <option value="smoking" {{ $area === 'smoking' ? 'selected' : '' }}>Smoking</option>
                    <option value="non-smoking" {{ $area === 'non-smoking' ? 'selected' : '' }}>Non-Smoking</option>
                </select>
                @error('area')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select wire:model.defer="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Status --</option>
                    <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="occupied" {{ $status === 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="reserved" {{ $status === 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="maintenance" {{ $status === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
                @error('status')
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
