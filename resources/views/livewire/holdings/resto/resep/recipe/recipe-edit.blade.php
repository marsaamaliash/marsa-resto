<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Edit Resep</h2>

    <form wire:submit.prevent="update" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Resep</label>
            <input type="text" wire:model.defer="recipe_code"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Auto-generate jika kosong">
            @error('recipe_code')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Resep <span class="text-red-500">*</span></label>
            <input type="text" wire:model.defer="recipe_name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('recipe_name')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Resep <span class="text-red-500">*</span></label>
                <select wire:model.defer="recipe_type"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="menu">Menu (Final)</option>
                    <option value="preparation">Preparation (Bumbu Base)</option>
                    <option value="additional">Additional (Bundling)</option>
                </select>
                @error('recipe_type')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Issue Method</label>
                <select wire:model.defer="issue_method"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="batch_actual">Batch Actual</option>
                    <option value="manual">Manual</option>
                    <option value="fifo">FIFO</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Output <span class="text-red-500">*</span></label>
                <select wire:model.defer="output_item_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Item --</option>
                    @foreach ($items as $item)
                        <option value="{{ $item['value'] }}" {{ $item['value'] == $output_item_id ? 'selected' : '' }}>{{ $item['label'] }}</option>
                    @endforeach
                </select>
                @error('output_item_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan Default <span class="text-red-500">*</span></label>
                <select wire:model.defer="default_uom_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Satuan --</option>
                    @foreach ($uoms as $uom)
                        <option value="{{ $uom['value'] }}" {{ $uom['value'] == $default_uom_id ? 'selected' : '' }}>{{ $uom['label'] }}</option>
                    @endforeach
                </select>
                @error('default_uom_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Yield Tracking</label>
            <select wire:model.defer="yield_tracking_mode"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="strict">Strict</option>
                <option value="flexible">Flexible</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea wire:model.defer="notes"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                rows="2" placeholder="Opsional">{{ $notes }}</textarea>
            @error('notes')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model.defer="is_active" id="edit_is_active"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="edit_is_active" class="text-sm text-gray-700">Aktif</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Simpan
            </button>
            <button type="button" wire:click="cancel"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Batal
            </button>
        </div>
    </form>

    @if ($toast['show'])
        <div class="mt-4 p-3 rounded {{ $toast['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $toast['message'] }}
        </div>
    @endif
</div>