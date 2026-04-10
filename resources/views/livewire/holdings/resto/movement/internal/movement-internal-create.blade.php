<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Request Movement: Gudang → Dapur</h2>

    <form wire:submit.prevent="store" class="space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Gudang <span class="text-red-500">*</span></label>
                <select wire:model.defer="from_location_id" wire:change="onFromLocationChanged"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0">-- Pilih Gudang --</option>
                    @foreach ($fromLocations as $loc)
                        <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                    @endforeach
                </select>
                @error('from_location_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ke Dapur <span class="text-red-500">*</span></label>
                <select wire:model.defer="to_location_id"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0">-- Pilih Dapur --</option>
                    @foreach ($toLocations as $loc)
                        <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                    @endforeach
                </select>
                @error('to_location_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC</label>
                <input type="text" wire:model.defer="pic_name"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Nama Penanggung Jawab">
            </div>
        </div>

        <div>
            <div class="flex justify-between items-center mb-2">
                <label class="block text-sm font-medium text-gray-700">Daftar Item</label>
                <button type="button" wire:click="addItemRow"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    + Tambah Item
                </button>
            </div>

            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-28">Qty</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-20">Satuan</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($items as $index => $item)
                            <tr>
                                <td class="px-3 py-2">
                                    <select wire:model.defer="items.{{ $index }}.item_id" 
                                        wire:change="onItemChanged({{ $index }})"
                                        class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="0">-- Pilih Item --</option>
                                        @foreach($availableItems as $availItem)
                                            <option value="{{ $availItem['id'] }}">
                                                {{ $availItem['name'] }} ({{ $availItem['sku'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($item['item_id'] > 0 && $from_location_id > 0)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Tersedia: <span class="font-medium text-green-600">{{ number_format($item['available_qty'], 2) }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" step="0.01" min="0.01" 
                                        wire:model.defer="items.{{ $index }}.qty"
                                        class="w-full border-gray-300 rounded-md text-sm text-right focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="0">
                                </td>
                                <td class="px-3 py-2 text-center text-sm">
                                    @php
                                        $selectedItem = collect($availableItems)->firstWhere('id', $item['item_id']);
                                    @endphp
                                    {{ $selectedItem['uom_symbols'] ?? '-' }}
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" 
                                        wire:model.defer="items.{{ $index }}.remark"
                                        class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="Catatan">
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if(count($items) > 1)
                                        <button type="button" wire:click="removeItemRow({{ $index }})"
                                            class="text-red-600 hover:text-red-800 text-sm" title="Hapus">
                                            ✕
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea wire:model.defer="remark" rows="2"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Catatan_opsional..."></textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Simpan Request
            </button>
            <button type="button" wire:click="cancel"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Batal
            </button>
        </div>
    </form>

    @if($toast['show'])
    <div class="fixed top-4 right-4 z-50">
        <div class="px-4 py-3 rounded-lg shadow-lg {{ $toast['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' }} text-white">
            {{ $toast['message'] }}
            <button wire:click="$set('toast.show', false)" class="ml-2 font-bold">✕</button>
        </div>
    </div>
    @endif
</div>