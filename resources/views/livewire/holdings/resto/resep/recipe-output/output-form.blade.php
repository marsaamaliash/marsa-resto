<div class="p-6">
    <h2 class="text-xl font-bold mb-4">{{ $outputId ? 'Edit Output' : 'Add Output Recipe' }}</h2>

    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type Output <span class="text-red-500">*</span></label>
                <select wire:model.defer="outputType"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="main">Main (Utama)</option>
                    <option value="by_product">By-Product</option>
                    <option value="co_product">Co-Product</option>
                    <option value="waste">Waste</option>
                </select>
                @error('outputType')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Output Item <span class="text-red-500">*</span></label>
                <select wire:model.defer="output_item_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Item --</option>
                    @foreach ($items as $item)
                        <option value="{{ $item['value'] }}">{{ $item['label'] }}</option>
                    @endforeach
                </select>
                @error('output_item_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Planned Qty <span class="text-red-500">*</span></label>
                <input type="number" step="0.000001" min="0" wire:model.defer="plannedQty"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="0">
                @error('plannedQty')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                <select wire:model.defer="uom_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select Unit --</option>
                    @foreach ($uoms as $uom)
                        <option value="{{ $uom['value'] }}">{{ $uom['label'] }}</option>
                    @endforeach
                </select>
                @error('uom_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Allocation %</label>
                <input type="number" step="0.01" min="0" max="100" wire:model.defer="costAllocationPct"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="100">
                @error('costAllocationPct')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-end">
                <div class="flex items-center gap-2 pb-1">
                    <input type="checkbox" wire:model.defer="isInventoryItem" id="out_is_Inventory"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="out_is_Inventory" class="text-sm text-gray-700">Masuk ke Inventory</label>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model.defer="notes"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                rows="2" placeholder="Optional"></textarea>
            @error('notes')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                {{ $outputId ? 'Perbarui' : 'Save' }}
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