<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Edit Item</h2>

    <form wire:submit.prevent="update" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
            <input type="text" wire:model.defer="name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. Granulated Sugar">
            @error('name')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU / Barcode <span class="text-red-500">*</span></label>
            <input type="text" wire:model.defer="sku"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="e.g. GLP-001">
            @error('sku')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea wire:model.defer="description"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                rows="2" placeholder="Optional"></textarea>
            @error('description')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type Item <span class="text-red-500">*</span></label>
                <select wire:model.defer="type"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @foreach ($typeOptions as $opt)
                        <option value="{{ $opt['value'] }}" {{ $opt['value'] === $type ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
                @error('type')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                <select wire:model.defer="category_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Category --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat['value'] }}" {{ $cat['value'] == $category_id ? 'selected' : '' }}>{{ $cat['label'] }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
            <select wire:model.defer="uom_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Select Unit --</option>
                @foreach ($uoms as $uom)
                    <option value="{{ $uom['value'] }}" {{ $uom['value'] == $uom_id ? 'selected' : '' }}>{{ $uom['label'] }}</option>
                @endforeach
            </select>
            @error('uom_id')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.live="is_active" id="edit_is_active"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="edit_is_active" class="text-sm text-gray-700">Active</label>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.live="is_stockable" id="edit_is_stockable"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="edit_is_stockable" class="text-sm text-gray-700">Stockable</label>
            </div>
        </div>

        @if ($is_stockable)
            <div class="border-t pt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Min. Stock <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" wire:model.defer="min_stock"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="0">
                    @error('min_stock')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="has_batch" id="edit_has_batch"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="edit_has_batch" class="text-sm text-gray-700">Batch</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model.defer="has_expiry" id="edit_has_expiry"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="edit_has_expiry" class="text-sm text-gray-700">Expiry</label>
                    </div>
                </div>
            </div>
        @endif

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
