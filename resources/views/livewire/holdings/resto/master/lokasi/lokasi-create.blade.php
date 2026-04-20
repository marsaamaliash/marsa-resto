<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Tambah Lokasi</h2>

    <form wire:submit.prevent="store" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="name"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Contoh: Gudang Utama">
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                <input type="text" wire:model.defer="code"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Contoh: WH-01">
                @error('code')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe <span class="text-red-500">*</span></label>
                <select wire:model.defer="type" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih --</option>
                    <option value="warehouse">Warehouse</option>
                    <option value="kitchen">Kitchen</option>
                    <option value="outlet">Outlet</option>
                    <option value="transit">Transit</option>
                </select>
                @error('type')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="pic_name"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Contoh: Budi Santoso">
                @error('pic_name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea wire:model.defer="notes"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Catatan tambahan (opsional)" rows="2"></textarea>
            @error('notes')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" wire:model.defer="is_active" id="is_active"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="is_active" class="text-sm text-gray-700">Aktif</label>
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
