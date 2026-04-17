<div class="p-6">
    <h2 class="text-xl font-bold mb-4">
        @if ($isSemiFinished)
            Tambah Resep Semi-Finished
        @else
            Tambah Resep Menu
        @endif
    </h2>

    <form wire:submit.prevent="store" class="space-y-6">
        
        {{-- Menu Selection (only for menu recipes) --}}
        @if (! $isSemiFinished)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Menu <span class="text-red-500">*</span></label>
                <select wire:model="menu_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Pilih Menu --</option>
                    @foreach ($availableMenus as $menu)
                        <option value="{{ $menu['value'] }}">{{ $menu['label'] }}</option>
                    @endforeach
                </select>
                @error('menu_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
                @if (empty($availableMenus))
                    <p class="text-sm text-yellow-600 mt-1">Semua menu sudah memiliki resep aktif.</p>
                @endif
            </div>
        @endif

        {{-- Recipe Name --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                @if ($isSemiFinished)
                    Nama Resep Semi-Finished <span class="text-red-500">*</span>
                @else
                    Nama Resep <span class="text-red-500">*</span>
                @endif
            </label>
            <input type="text" wire:model="recipe_name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="{{ $isSemiFinished ? 'Contoh: Bumbu Ayam Bakar' : 'Contoh: Nasi Goreng Original' }}">
            @error('recipe_name')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Version Notes --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Versi</label>
            <textarea wire:model="version_notes"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                rows="2" placeholder="Opsional: kenapa versi ini dibuat"></textarea>
            @error('version_notes')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Components Section --}}
        <div class="border-t pt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-medium text-gray-800">Bahan Resep (BOM) <span class="text-red-500">*</span></h3>
                <button type="button" wire:click="addComponent"
                    class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                    + Tambah Bahan
                </button>
            </div>

            @if (empty($components))
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-center text-gray-500">
                    Belum ada bahan. Klik "Tambah Bahan" untuk menambahkan item dari kitchen atau resep semi-finished.
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($components as $index => $component)
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-3">
                            {{-- Component Type Selection --}}
                            <div class="flex gap-4 mb-3">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                        name="component_type_{{ $index }}"
                                        value="item"
                                        wire:model.live="components.{{ $index }}.component_type"
                                        class="mr-2">
                                    <span class="text-sm">Item (Kitchen)</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" 
                                        name="component_type_{{ $index }}"
                                        value="recipe"
                                        wire:model.live="components.{{ $index }}.component_type"
                                        class="mr-2">
                                    <span class="text-sm">Resep Semi-Finished</span>
                                </label>
                            </div>

                            <div class="flex gap-3 items-start">
                                @if ($component['component_type'] === 'item')
                                    {{-- Item Selection --}}
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Item Bahan</label>
                                        <select wire:model="components.{{ $index }}.item_id"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">-- Pilih Item --</option>
                                            @foreach ($kitchenItems as $item)
                                                <option value="{{ $item['item_id'] }}">
                                                    {{ $item['item_name'] }} ({{ $item['qty_available'] }} {{ $item['uom_name'] }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("components.{$index}.item_id")
                                            <span class="text-red-600 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="w-32">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                                        <input type="number" step="0.01" wire:model="components.{{ $index }}.qty"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="0.00">
                                        @error("components.{$index}.qty")
                                            <span class="text-red-600 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="w-32">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                                        <input type="text" wire:model="components.{{ $index }}.uom_name" readonly
                                            class="w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-100">
                                        <input type="hidden" wire:model="components.{{ $index }}.uom_id">
                                        @error("components.{$index}.uom_id")
                                            <span class="text-red-600 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @else
                                    {{-- Recipe Selection --}}
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Resep Semi-Finished</label>
                                        <select wire:model="components.{{ $index }}.recipe_id"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">-- Pilih Resep --</option>
                                            @foreach ($semiFinishedRecipes as $recipe)
                                                <option value="{{ $recipe['recipe_id'] }}">
                                                    {{ $recipe['recipe_code'] }} - {{ $recipe['recipe_name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error("components.{$index}.recipe_id")
                                            <span class="text-red-600 text-xs">{{ $message }}</span>
                                        @enderror
                                        @if (empty($semiFinishedRecipes))
                                            <p class="text-xs text-yellow-600 mt-1">Belum ada resep semi-finished. Buat dulu di halaman resep.</p>
                                        @endif
                                    </div>

                                    <div class="w-32">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                                        <input type="number" step="0.01" wire:model="components.{{ $index }}.qty"
                                            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="0.00">
                                        @error("components.{$index}.qty")
                                            <span class="text-red-600 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="w-32">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Satuan</label>
                                        <input type="text" value="Unit" readonly
                                            class="w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-100">
                                    </div>
                                @endif

                                <div class="pt-6">
                                    <button type="button" wire:click="removeComponent({{ $index }})"
                                        class="text-red-600 hover:text-red-800 p-1"
                                        title="Hapus bahan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @error('components')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex gap-3 pt-4 border-t">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                @if ($isSemiFinished)
                    Simpan Resep Semi-Finished
                @else
                    Simpan Resep
                @endif
            </button>
            <button type="button" wire:click="cancel"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">
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
