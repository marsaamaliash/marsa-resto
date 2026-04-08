<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Tambah Item</h2>

    <form wire:submit.prevent="store" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Item</label>
            <input type="text" wire:model.defer="name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Contoh: Gula Pasir">
            @error('name')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU / Barcode</label>
            <input type="text" wire:model.defer="sku"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Contoh: GLP-001">
            @error('sku')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea wire:model.defer="description"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                rows="2" placeholder="Opsional"></textarea>
            @error('description')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select wire:model.defer="category_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat['value'] }}">{{ $cat['label'] }}</option>
                    @endforeach
                </select>
                @error('category_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                <select wire:model.defer="uom_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Satuan --</option>
                    @foreach ($uoms as $uom)
                        <option value="{{ $uom['value'] }}">{{ $uom['label'] }}</option>
                    @endforeach
                </select>
                @error('uom_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Min. Stok</label>
            <input type="number" step="0.01" min="0" wire:model.defer="min_stock"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="0">
            @error('min_stock')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.defer="is_active" id="is_active"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_active" class="text-sm text-gray-700">Aktif</label>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.defer="is_stockable" id="is_stockable"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_stockable" class="text-sm text-gray-700">Stokable</label>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.defer="has_batch" id="has_batch"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="has_batch" class="text-sm text-gray-700">Batch</label>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.defer="has_expiry" id="has_expiry"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="has_expiry" class="text-sm text-gray-700">Expiry</label>
            </div>
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
</div>
