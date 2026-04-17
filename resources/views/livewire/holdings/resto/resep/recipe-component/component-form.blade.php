<div class="p-6">
    <h2 class="text-xl font-bold mb-4">{{ $componentId ? 'Edit Komponen BOM' : 'Tambah Komponen BOM' }}</h2>

    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Komponen <span class="text-red-500">*</span></label>
                <select wire:model.live="componentKind"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="item">Item (Bahan Baku)</option>
                    <option value="recipe">Sub-Resep (Nested BOM)</option>
                </select>
                @error('componentKind')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stage</label>
                <select wire:model.defer="stageCode"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="main">Main</option>
                    <option value="prep">Preparation</option>
                    <option value="cooking">Cooking</option>
                    <option value="finishing">Finishing</option>
                </select>
            </div>
        </div>

        @if ($componentKind === 'item')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Bahan <span class="text-red-500">*</span></label>
                <select wire:model.defer="component_item_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Item --</option>
                    @foreach ($items as $item)
                        <option value="{{ $item['value'] }}">{{ $item['label'] }}</option>
                    @endforeach
                </select>
                @error('component_item_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
        @else
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sub-Resep <span class="text-red-500">*</span></label>
                <select wire:model.defer="component_recipe_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Resep --</option>
                    @foreach ($recipes as $recipe)
                        <option value="{{ $recipe['value'] }}">{{ $recipe['label'] }}</option>
                    @endforeach
                </select>
                @error('component_recipe_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
                <p class="text-xs text-amber-600 mt-1">
                    &#9888; Sub-resep akan menyebabkan nested BOM. Pastikan tidak ada circular reference.
                </p>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Qty Standard <span class="text-red-500">*</span></label>
                <input type="number" step="0.000001" min="0" wire:model.defer="qtyStandard"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="0">
                @error('qtyStandard')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
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

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Wastage %</label>
                <input type="number" step="0.01" min="0" max="100" wire:model.defer="wastagePctStandard"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="0">
                @error('wastagePctStandard')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex flex-col justify-end gap-4 pt-5">
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model.defer="isOptional" id="comp_is_optional"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="comp_is_optional" class="text-sm text-gray-700">Opsional</label>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model.defer="isModifierDriven" id="comp_is_modifier_driven"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="comp_is_modifier_driven" class="text-sm text-gray-700">Modifier Driven</label>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea wire:model.defer="notes"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                rows="2" placeholder="Opsional"></textarea>
            @error('notes')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                {{ $componentId ? 'Perbarui' : 'Simpan' }}
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