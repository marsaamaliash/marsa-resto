<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Add Repack Stok</h2>

    @if ($toast['show'])
        <div class="mb-4 p-3 rounded-lg {{ $toast['type'] === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
            {{ $toast['message'] }}
        </div>
    @endif

    <form wire:submit.prevent="store" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location <span class="text-red-500">*</span></label>
            <select wire:model.defer="location_id"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">-- Select Location --</option>
                @foreach ($locations as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            @error('location_id')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Source Item <span class="text-red-500">*</span></label>
                <select wire:model.defer="source_item_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Item --</option>
                    @foreach ($items as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @error('source_item_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target Item <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-2">
                    <select wire:model.defer="target_item_id"
                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Select Item --</option>
                        @foreach ($items as $id => $name)
                            <option value="{{ $id }}">{{ $name }} ({{ $uoms[$itemUoms[$id] ?? 1] }})</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="openNewItemModal"
                        class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                        title="Add Item Baru">
                        +
                    </button>
                </div>
                @error('target_item_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Source Qty <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" min="0.01" wire:model.defer="qty_source_taken"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. 1">
                @error('qty_source_taken')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Multiplier <span class="text-red-500">*</span></label>
                <input type="number" step="1" min="1" wire:model.defer="multiplier"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="e.g. 24">
                @error('multiplier')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Fill per unit (bottle/package per carton)</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model.defer="notes" rows="2"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Optional..."></textarea>
            @error('notes')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
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

    @if ($showNewItemModal)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeNewItemModal"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl relative">
                <button type="button" wire:click="closeNewItemModal"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Close">
                    <span class="text-xl leading-none">✕</span>
                </button>
                <div class="p-6">
                    <h3 class="text-lg font-bold mb-4">Add Item Baru</h3>
                    <form wire:submit.prevent="saveNewItem" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="newItemName"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Item Name">
                            @error('newItemName')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU / Barcode <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="newItemSku"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="SKU atau barcode">
                            @error('newItemSku')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model.defer="newItemDescription" rows="2"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Optional..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                            <select wire:model.defer="newItemCategoryId"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Select Category --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat['value'] }}">{{ $cat['label'] }}</option>
                                @endforeach
                            </select>
                            @error('newItemCategoryId')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Min Stock</label>
                                <input type="number" step="0.01" min="0" wire:model.defer="newItemMinStock"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                                <select wire:model.defer="newItemType"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="raw">Raw</option>
                                    <option value="prep">Prep</option>
                                    <option value="menu">Menu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                                <select wire:model.defer="newItemUomId"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Select --</option>
                                    @foreach ($uoms as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('newItemUomId')
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="flex gap-3 pt-4">
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                Save
                            </button>
                            <button type="button" wire:click="closeNewItemModal"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>