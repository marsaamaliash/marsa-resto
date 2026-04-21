<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Add Unit Conversion</h2>

    <form wire:submit.prevent="store" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item <span class="text-red-500">*</span></label>
            <select wire:model.defer="item_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Select Item --</option>
                @foreach ($items as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('item_id')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Unit <span class="text-red-500">*</span></label>
            <select wire:model.defer="from_uom_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Select Unit --</option>
                @foreach ($uoms as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('from_uom_id')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To Unit <span class="text-red-500">*</span></label>
            <select wire:model.defer="to_uom_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Select Unit --</option>
                @foreach ($uoms as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('to_uom_id')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Conversion Value <span class="text-red-500">*</span></label>
            <input type="number" step="0.0001" min="0.0001" wire:model.defer="conversion_factor"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. 1000">
            @error('conversion_factor')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Example: 1 kg = 1000 g, then conversion value = 1000</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model.defer="notes" rows="3"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Additional notes..."></textarea>
            @error('notes')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Save
            </button>
            <button type="button" wire:click="saveDraft"
                class="px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                Draft
            </button>
            <button type="button" wire:click="cancel"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancel
            </button>
        </div>
    </form>

    @if ($toast['show'])
        <div class="mt-4 px-4 py-2 rounded-md
            {{ $toast['type'] === 'success' ? 'bg-green-100 text-green-800' : ($toast['type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
            {{ $toast['message'] }}
        </div>
    @endif
</div>
