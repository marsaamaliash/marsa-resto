<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Tambah Meja</h2>

    <form wire:submit.prevent="store" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Meja <span class="text-red-500">*</span></label>
                <input type="text" wire:model.defer="table_number"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Contoh: 01">
                @error('table_number')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas <span class="text-red-500">*</span></label>
                <input type="number" wire:model.defer="capacity" min="1" max="50"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Contoh: 4">
                @error('capacity')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Area <span class="text-red-500">*</span></label>
                <select wire:model.defer="area" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Area --</option>
                    <option value="indoor">Indoor</option>
                    <option value="outdoor">Outdoor</option>
                    <option value="vip">VIP</option>
                    <option value="smoking">Smoking</option>
                    <option value="non-smoking">Non-Smoking</option>
                </select>
                @error('area')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <select wire:model.defer="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Status --</option>
                    <option value="available">Tersedia</option>
                    <option value="occupied">Terisi</option>
                    <option value="reserved">Direservasi</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                @error('status')
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
            <button type="button" wire:click="saveDraft"
                class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600">
                Save as Draft
            </button>
            <button type="button" wire:click="cancel"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Batal
            </button>
        </div>
    </form>
</div>
