<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Add Unit</h2>

    <form wire:submit.prevent="store" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Name <span class="text-red-500">*</span></label>
            <input type="text" wire:model.defer="name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. Kilogram">
            @error('name')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Symbol <span class="text-red-500">*</span></label>
            <input type="text" wire:model.defer="symbols"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. kg">
            @error('symbols')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select wire:model.defer="type"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Select Type --</option>
                @foreach ($typeOptions as $opt)
                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                @endforeach
            </select>
            @error('type')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model.defer="is_active" id="is_active"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="is_active" class="text-sm text-gray-700">Active</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Save
            </button>
            <button type="button" wire:click="saveDraft"
                class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600">
                Save as Draft
            </button>
            <button type="button" wire:click="cancel"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Cancel
            </button>
        </div>
    </form>

    @if ($toast['show'])
        <div class="mt-4 p-3 rounded {{ $toast['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $toast['message'] }}
        </div>
    @endif
</div>
