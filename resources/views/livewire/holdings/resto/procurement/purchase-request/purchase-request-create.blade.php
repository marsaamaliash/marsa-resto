<x-ui.sccr-card transparent wire:key="purchase-request-create" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    {{ $isEditMode ? 'Edit Purchase Request' : 'Buat Purchase Request Baru' }}
                </h1>
                <p class="text-blue-100 text-sm">
                    {{ $isEditMode ? 'Revisi Purchase Request yang sudah ada' : 'Pilih stok kritis atau tambah item lain untuk dibeli' }}
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    {{-- ================= MAIN FORM ================= --}}
    <div class="flex-1 min-h-0 px-4 py-4 overflow-auto">
        <div class="max-w-6xl mx-auto space-y-6">

            {{-- LOCATION SELECTION --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Lokasi Request</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Lokasi <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="selectedLocationId"
                            class="w-full border-gray-300 rounded-md shadow-sm"
                            {{ $isEditMode ? 'disabled' : '' }}>
                            <option value="0">-- Pilih Lokasi --</option>
                            @foreach ($this->locations as $loc)
                                <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                            @endforeach
                        </select>
                        @if ($isEditMode)
                            <p class="text-xs text-gray-500 mt-1">Lokasi tidak dapat diubah saat revisi.</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            Tanggal Dibutuhkan
                        </label>
                        <input type="date" wire:model="requiredDate"
                            class="w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        Catatan PR
                    </label>
                    <textarea wire:model="notes" rows="2"
                        class="w-full border-gray-300 rounded-md shadow-sm"
                        placeholder="Catatan umum untuk PR ini..."></textarea>
                </div>
            </div>

            {{-- TABS --}}
            @if ($selectedLocationId > 0)
                <div class="bg-white rounded-xl shadow border overflow-hidden">
                    {{-- TAB HEADERS --}}
                    <div class="flex border-b">
                        <button wire:click="$set('showCriticalTab', true)"
                            class="px-6 py-3 text-sm font-bold {{ $showCriticalTab ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="flex items-center gap-2">
                                <x-ui.sccr-icon name="alert-triangle" :size="16" class="text-red-500" />
                                Stok Kritis
                                @if (count($criticalItems) > 0)
                                    <span class="bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded-full">
                                        {{ count($criticalItems) }}
                                    </span>
                                @endif
                            </span>
                        </button>
                        <button wire:click="$set('showCriticalTab', false)"
                            class="px-6 py-3 text-sm font-bold {{ !$showCriticalTab ? 'text-blue-600 border-b-2 border-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="flex items-center gap-2">
                                <x-ui.sccr-icon name="plus-circle" :size="16" />
                                Tambah Item Lain
                                @if (count($additionalItems) > 0)
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full">
                                        {{ count($additionalItems) }}
                                    </span>
                                @endif
                            </span>
                        </button>
                    </div>

                    {{-- TAB CONTENT: CRITICAL STOCK --}}
                    @if ($showCriticalTab)
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold text-gray-800">Daftar Stok Kritis</h3>
                                <p class="text-sm text-gray-600">
                                    Item dengan stok aktual di bawah minimum
                                </p>
                            </div>

                            @if (count($criticalItems) === 0)
                                <div class="text-center py-10 bg-gray-50 rounded-lg">
                                    <x-ui.sccr-icon name="check-circle" :size="48" class="text-green-500 mx-auto mb-3" />
                                    <p class="text-gray-600 font-medium">Tidak ada stok kritis di lokasi ini</p>
                                    <p class="text-sm text-gray-500 mt-1">Semua item memiliki stok di atas minimum</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Pilih</th>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Item</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase">Stok Aktual</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase">Min Stok</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase">Defisit</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase">Qty Order</th>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach ($criticalItems as $item)
                                                @php
                                                    $isSelected = isset($selectedCriticalItems[$item['id']]);
                                                @endphp
                                                <tr class="{{ $isSelected ? 'bg-blue-50' : '' }}">
                                                    <td class="px-3 py-3">
                                                        <input type="checkbox"
                                                            wire:click="toggleCriticalItem({{ $item['id'] }})"
                                                            {{ $isSelected ? 'checked' : '' }}
                                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                                        <div class="text-xs text-gray-500">SKU: {{ $item['sku'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono {{ $item['actual_stock'] < $item['min_stock'] ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                                            {{ number_format($item['actual_stock'], 2) }}
                                                        </span>
                                                        <span class="text-xs text-gray-500">{{ $item['uom'] }}</span>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono text-gray-900">
                                                            {{ number_format($item['min_stock'], 2) }}
                                                        </span>
                                                        <span class="text-xs text-gray-500">{{ $item['uom'] }}</span>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono text-red-600 font-bold">
                                                            {{ number_format($item['deficit'], 2) }}
                                                        </span>
                                                        <span class="text-xs text-gray-500">{{ $item['uom'] }}</span>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        @if ($isSelected)
                                                            <input type="number"
                                                                step="0.01"
                                                                min="0.01"
                                                                wire:change="updateCriticalQty({{ $item['id'] }}, $event.target.value)"
                                                                value="{{ $selectedCriticalItems[$item['id']]['qty'] }}"
                                                                class="w-24 border-gray-300 rounded-md text-sm text-right">
                                                            <span class="text-xs text-gray-500 ml-1">{{ $item['uom'] }}</span>
                                                        @else
                                                            <span class="text-sm text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        @if ($isSelected)
                                                            <input type="text"
                                                                wire:model="selectedCriticalItems.{{ $item['id'] }}.notes"
                                                                class="w-full border-gray-300 rounded-md text-sm"
                                                                placeholder="Catatan item...">
                                                        @else
                                                            <span class="text-sm text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                    {{-- TAB CONTENT: ADDITIONAL ITEMS --}}
                    @else
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold text-gray-800">Item Tambahan (Non-Kritis)</h3>
                                <p class="text-sm text-gray-600">
                                    Tambahkan item lain yang ingin dipesan
                                </p>
                            </div>

                            {{-- ADD ITEM DROPDOWN --}}
                            <div class="mb-6 flex gap-2">
                                <select id="addItemSelect" class="flex-1 border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- Pilih Item --</option>
                                    @foreach ($this->availableItems as $availItem)
                                        <option value="{{ $availItem['id'] }}">
                                            {{ $availItem['name'] }} ({{ $availItem['sku'] }})
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    onclick="const select = document.getElementById('addItemSelect'); if(select.value) { $wire.addAdditionalItem(select.value); select.value = ''; }"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                    <x-ui.sccr-icon name="plus" :size="16" class="inline mr-1" />
                                    Tambah
                                </button>
                            </div>

                            {{-- ADDITIONAL ITEMS TABLE --}}
                            @if (count($additionalItems) === 0)
                                <div class="text-center py-8 bg-gray-50 rounded-lg">
                                    <p class="text-gray-500">Belum ada item tambahan</p>
                                    <p class="text-sm text-gray-400 mt-1">Pilih item dari dropdown di atas</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Item</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase w-32">Qty</th>
                                                <th class="px-3 py-3 text-left text-xs font-bold text-gray-500 uppercase">Catatan</th>
                                                <th class="px-3 py-3 text-center text-xs font-bold text-gray-500 uppercase w-16">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach ($additionalItems as $index => $item)
                                                <tr>
                                                    <td class="px-3 py-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                                        <div class="text-xs text-gray-500">Satuan: {{ $item['uom'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <input type="number"
                                                            step="0.01"
                                                            min="0.01"
                                                            wire:change="updateAdditionalQty({{ $index }}, $event.target.value)"
                                                            value="{{ $item['qty'] }}"
                                                            class="w-24 border-gray-300 rounded-md text-sm text-right">
                                                        <span class="text-xs text-gray-500 ml-1">{{ $item['uom'] }}</span>
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <input type="text"
                                                            wire:model="additionalItems.{{ $index }}.notes"
                                                            class="w-full border-gray-300 rounded-md text-sm"
                                                            placeholder="Catatan item...">
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <button type="button"
                                                            wire:click="removeAdditionalItem({{ $index }})"
                                                            class="text-red-600 hover:text-red-800"
                                                            title="Hapus">
                                                            <x-ui.sccr-icon name="trash" :size="18" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- SUMMARY & ACTIONS --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Ringkasan PR</h3>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">{{ count($selectedCriticalItems) }}</span> item stok kritis +
                                <span class="font-semibold">{{ count($additionalItems) }}</span> item tambahan
                            </p>
                            @if (count($additionalItems) > 0)
                                <p class="text-xs text-orange-600 mt-1">
                                    * Item non-kritis akan dilaporkan ke RM melalui field catatan
                                </p>
                            @endif
                        </div>

                        <div class="flex gap-3">
                            <x-ui.sccr-button type="button" wire:click="cancel"
                                class="bg-gray-500 text-white hover:bg-gray-600">
                                Batal
                            </x-ui.sccr-button>

                            <x-ui.sccr-button type="button" wire:click="saveAsDraft"
                                class="bg-gray-700 text-white hover:bg-gray-800">
                                Simpan Draft
                            </x-ui.sccr-button>

                            <x-ui.sccr-button type="button" wire:click="submitToRM"
                                class="bg-blue-600 text-white hover:bg-blue-700">
                                Submit ke RM
                            </x-ui.sccr-button>
                        </div>
                    </div>
                </div>
            @else
                {{-- NO LOCATION SELECTED --}}
                <div class="bg-yellow-50 rounded-xl shadow border border-yellow-200 p-8 text-center">
                    <x-ui.sccr-icon name="map-pin" :size="48" class="text-yellow-500 mx-auto mb-3" />
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Pilih Lokasi Terlebih Dahulu</h3>
                    <p class="text-gray-600">Silakan pilih lokasi untuk melihat daftar stok kritis</p>
                </div>
            @endif

        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

</x-ui.sccr-card>
