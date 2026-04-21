<x-ui.sccr-card transparent wire:key="goods-receipt-create" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-green-600 to-green-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Buat Goods Receipt</h1>
                <p class="text-green-100 text-sm mt-1">Input penerimaan barang dari Purchase Order</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    {{-- ================= MAIN FORM ================= --}}
    <div class="flex-1 min-h-0 px-4 py-4 overflow-auto">
        <div class="max-w-6xl mx-auto space-y-6">

            {{-- TOAST NOTIFICATION --}}
            @if ($toast['show'])
                <div class="fixed top-20 right-4 z-50">
                    <div class="px-6 py-4 rounded-lg shadow-lg {{ $toast['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' }} text-white">
                        {{ $toast['message'] }}
                    </div>
                </div>
            @endif

            {{-- STEP 1: SELECT PO --}}
            @if ($showPOSelector)
                <div class="bg-white rounded-xl shadow border p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Pilih Purchase Order</h2>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">PO yang Approved <span class="text-red-500">*</span></label>
                        <select wire:model.live="selectedPOId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="0">-- Pilih PO --</option>
                            @foreach ($availablePOs as $po)
                                <option value="{{ $po['id'] }}">{{ $po['text'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($selectedPOId)
                        <div class="mt-4 flex justify-end">
                            <x-ui.sccr-button type="button" wire:click="loadPOItems"
                                class="bg-green-600 text-white hover:bg-green-700">
                                Load Items
                            </x-ui.sccr-button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- STEP 2: INPUT RECEIVED ITEMS --}}
            @if (! $showPOSelector && $receiptId)
                <div class="bg-white rounded-xl shadow border p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Input Penerimaan Barang</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold text-gray-700">Item</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Qty Ordered</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">UoM</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Received (Baik)</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Damaged/Rusak</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Expired</th>
                                    <th class="px-3 py-2 text-left font-bold text-gray-700">Catatan Kondisi</th>
                                    <th class="px-3 py-2 text-center font-bold text-gray-700">Dokumentasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($itemsData as $key => $item)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-3 py-2">{{ $item['item_name'] }}</td>
                                        <td class="px-3 py-2 text-center font-semibold">{{ $item['ordered_qty'] }}</td>
                                        <td class="px-3 py-2 text-center">{{ $item['uom'] }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" wire:model.live="itemsData.{{ $key }}.received_qty" step="0.01" min="0"
                                                class="w-20 px-2 py-1 border border-gray-300 rounded text-center text-sm">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" wire:model.live="itemsData.{{ $key }}.damaged_qty" step="0.01" min="0"
                                                class="w-20 px-2 py-1 border border-red-300 rounded text-center text-sm">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" wire:model.live="itemsData.{{ $key }}.expired_qty" step="0.01" min="0"
                                                class="w-20 px-2 py-1 border border-orange-300 rounded text-center text-sm">
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="text" wire:model.live="itemsData.{{ $key }}.condition_notes"
                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
                                                placeholder="Catatan kondisi...">
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="file" wire:model="documentationFiles.{{ $key }}"
                                                accept="image/*"
                                                class="text-xs">
                                            @error("documentationFiles.{{ $key }}") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- NOTES --}}
                    <div class="mt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catatan Umum</label>
                        <textarea wire:model="notes" rows="3"
                            placeholder="Catatan penerimaan barang (opsional)..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    </div>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex justify-end gap-4 pb-4">
                    <a href="{{ route('dashboard.resto.goods-receipt') }}"
                        class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                        Batal
                    </a>
                    <button type="button" wire:click="submitReceipt"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                        Simpan Penerimaan
                    </button>
                </div>
            @endif

        </div>
    </div>

</x-ui.sccr-card>
