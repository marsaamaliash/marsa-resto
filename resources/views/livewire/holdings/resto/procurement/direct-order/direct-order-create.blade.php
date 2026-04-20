<x-ui.sccr-card transparent wire:key="direct-order-create" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-teal-600 to-cyan-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Buat Direct Order</h1>
                <p class="text-teal-100 text-sm mt-1">Formulir Direct Order untuk kebutuhan mendadak</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    {{-- ================= FORM ================= --}}
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

            {{-- STEP 1: INFO --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Langkah 1: Informasi Direct Order</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Lokasi <span class="text-red-500">*</span></label>
                        <select wire:model.live="selectedLocationId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                            @foreach ($locations as $loc)
                                <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Pembeli <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="purchaserName"
                            placeholder="Nama yang melakukan pembelian"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        @error('purchaserName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Pembelian <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="purchaseDate"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        @error('purchaseDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pembayaran Dilakukan Oleh <span class="text-red-500">*</span></label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="paymentBy" value="holding"
                                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                <span class="ml-2 text-gray-700">Holding (Pusat)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model.live="paymentBy" value="resto"
                                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                <span class="ml-2 text-gray-700">Resto (Cabang)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- STEP 2: ITEMS --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-gray-800">Langkah 2: Detail Barang</h2>
                    <button type="button" wire:click="addRow"
                        class="px-3 py-1.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-semibold flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-3 py-2 text-left font-bold text-gray-700">Item <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-center font-bold text-gray-700">UoM</th>
                                <th class="px-3 py-2 text-center font-bold text-gray-700">Qty <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-right font-bold text-gray-700">Harga Satuan <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2 text-right font-bold text-gray-700">Total</th>
                                <th class="px-3 py-2 text-center font-bold text-gray-700 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $index => $row)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-3 py-2">
                                        <select wire:model.live="rows.{{ $index }}.item_id"
                                            class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                            <option value="0">-- Pilih Item --</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select wire:model.live="rows.{{ $index }}.uom_id"
                                            class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                            <option value="0">-- Pilih --</option>
                                            @foreach ($uoms as $uom)
                                                <option value="{{ $uom['id'] }}">{{ $uom['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" wire:model.live="rows.{{ $index }}.quantity" step="0.01" min="0"
                                            class="w-20 px-2 py-1 border border-gray-300 rounded text-center text-sm">
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" wire:model.live="rows.{{ $index }}.unit_price" step="0.01" min="0"
                                            class="w-32 px-2 py-1 border border-gray-300 rounded text-right text-sm">
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold">
                                        @php
                                            $price = (float) ($row['unit_price'] ?? 0);
                                            $qty = (float) ($row['quantity'] ?? 0);
                                        @endphp
                                        Rp {{ number_format($price * $qty, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @if (count($rows) > 1)
                                            <button type="button" wire:click="removeRow({{ $index }})"
                                                class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t">
                            <tr>
                                <td colspan="4" class="px-3 py-2 text-right font-bold text-gray-700">Grand Total:</td>
                                <td class="px-3 py-2 text-right font-bold text-teal-600">
                                    @php
                                        $grandTotal = 0;
                                        foreach ($rows as $row) {
                                            $grandTotal += (float) ($row['unit_price'] ?? 0) * (float) ($row['quantity'] ?? 0);
                                        }
                                    @endphp
                                    Rp {{ number_format($grandTotal, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- STEP 3: PROOF & NOTES --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Langkah 3: Upload Bukti & Catatan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Upload Bukti Pembelian <span class="text-red-500">*</span></label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-teal-500"
                            onclick="document.getElementById('proof-file-input').click()">
                            <input type="file" wire:model.live="proofFile"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="hidden" id="proof-file-input">
                            @if ($proofFile)
                                <div class="text-green-600 font-semibold mb-3">✓ {{ $proofFile->getClientOriginalName() }}</div>
                                @if (in_array($proofFile->extension(), ['jpg', 'jpeg', 'png']))
                                    <img src="{{ $proofFile->temporaryUrl() }}" class="max-w-full max-h-64 mx-auto rounded-lg shadow">
                                @elseif ($proofFile->extension() === 'pdf')
                                    <div class="text-sm text-gray-600">PDF: {{ round($proofFile->getSize() / 1024, 1) }} KB</div>
                                @endif
                            @else
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <p class="text-sm text-gray-600">Upload nota atau foto bukti pembelian</p>
                                <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG - Max 5MB</p>
                            @endif
                        </div>
                        @error('proofFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catatan</label>
                        <textarea wire:model="doNotes" rows="5"
                            placeholder="Catatan tambahan (opsional)..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                    </div>
                </div>
            </div>

            {{-- SUBMIT --}}
            <div class="flex justify-end gap-3 pb-8">
                <a href="{{ route('dashboard.resto.direct-order') }}"
                    class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold">
                    Kembali
                </a>
                <button type="button" wire:click="submitDO"
                    class="px-6 py-2.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-semibold">
                    Buat Direct Order
                </button>
            </div>

        </div>
    </div>

</x-ui.sccr-card>
