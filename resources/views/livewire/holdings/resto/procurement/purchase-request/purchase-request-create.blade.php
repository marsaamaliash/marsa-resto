<x-ui.sccr-card transparent wire:key="purchase-request-create" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">
                    {{ $isEditMode ? 'Edit Purchase Request' : 'Buat Purchase Request Baru' }}
                </h1>
                <p class="text-blue-100 text-sm mt-1">
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

            {{-- TOAST NOTIFICATION --}}
            @if ($toast['show'])
                <div class="fixed top-20 right-4 z-50">
                    <div class="px-6 py-4 rounded-lg shadow-lg {{ $toast['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' }} text-white">
                        {{ $toast['message'] }}
                    </div>
                </div>
            @endif

            {{-- STEP 1: LOCATION & INFO --}}
            <div class="bg-white rounded-xl shadow border p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Langkah 1: Informasi Lokasi & Request</h2>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Lokasi <span class="text-red-500">*</span></label>
                    <select wire:model.live="selectedLocationId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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

                <div class="mt-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Catatan PR</label>
                    <textarea wire:model="notes" rows="2"
                        placeholder="Catatan umum untuk PR ini..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>

            {{-- STEP 2: ITEMS (TABS) --}}
            @if ($selectedLocationId > 0)
                <div class="bg-white rounded-xl shadow border overflow-hidden">
                    <h2 class="text-lg font-bold text-gray-800 px-6 pt-6 pb-4">Langkah 2: Pilih Item</h2>

                    {{-- TAB HEADERS --}}
                    <div class="flex border-b px-6">
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
                                <h3 class="text-base font-bold text-gray-800">Daftar Stok Kritis</h3>
                                <p class="text-sm text-gray-600">Item dengan stok aktual di bawah atau mendekati minimum</p>
                            </div>

                            @if (count($criticalItems) === 0)
                                <div class="text-center py-10 bg-gray-50 rounded-lg">
                                    <x-ui.sccr-icon name="check-circle" :size="48" class="text-green-500 mx-auto mb-3" />
                                    <p class="text-gray-600 font-medium">Tidak ada stok kritis di lokasi ini</p>
                                    <p class="text-sm text-gray-500 mt-1">Semua item memiliki stok di atas minimum</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50 border-b">
                                            <tr>
                                                <th class="px-3 py-3 text-left font-bold text-gray-700">Pilih</th>
                                                <th wire:click="sortBy('item_name')" class="px-3 py-3 text-left font-bold text-gray-700 cursor-pointer hover:bg-gray-100">
                                                    Item {!! $sortField === 'item_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                                </th>
                                                <th wire:click="sortBy('actual_stock')" class="px-3 py-3 text-center font-bold text-gray-700 cursor-pointer hover:bg-gray-100">
                                                    Stok Sekarang {!! $sortField === 'actual_stock' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                                </th>
                                                <th wire:click="sortBy('min_stock')" class="px-3 py-3 text-center font-bold text-gray-700 cursor-pointer hover:bg-gray-100">
                                                    Min Stok {!! $sortField === 'min_stock' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                                </th>
                                                <th wire:click="sortBy('selisih')" class="px-3 py-3 text-center font-bold text-gray-700 cursor-pointer hover:bg-gray-100">
                                                    Selisih {!! $sortField === 'selisih' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                                </th>
                                                <th class="px-3 py-3 text-center font-bold text-gray-700">Status</th>
                                                <th class="px-3 py-3 text-center font-bold text-gray-700">Qty Order</th>
                                                <th class="px-3 py-3 text-left font-bold text-gray-700">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y bg-white">
                                            @foreach ($criticalItems as $item)
                                                @php
                                                    $isSelected = isset($selectedCriticalItems[$item['id']]);
                                                    $isCritical = $item['status'] === 'critical';
                                                    $isWarning = $item['status'] === 'warning';
                                                @endphp
                                                <tr class="hover:bg-gray-50 @if($isCritical) bg-red-50 @elseif($isWarning) bg-yellow-50 @endif">
                                                    <td class="px-3 py-3">
                                                        <input type="checkbox"
                                                            wire:click="toggleCriticalItem({{ $item['id'] }})"
                                                            {{ $isSelected ? 'checked' : '' }}
                                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item['sku'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono font-bold @if($isCritical) text-red-600 @elseif($isWarning) text-yellow-600 @endif">
                                                            {{ number_format($item['actual_stock'], 2) }}
                                                        </span>
                                                        <div class="text-xs text-gray-500">{{ $item['uom'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono text-gray-900">
                                                            {{ number_format($item['min_stock'], 2) }}
                                                        </span>
                                                        <div class="text-xs text-gray-500">{{ $item['uom'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono font-bold @if($item['deficit'] > 0) text-red-600 @else text-gray-500 @endif">
                                                            {{ number_format($item['deficit'], 2) }}
                                                        </span>
                                                        <div class="text-xs text-gray-500">{{ $item['uom'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        @if($isCritical)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                                Kritis
                                                            </span>
                                                        @elseif($isWarning)
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                                Warning
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        @if ($isSelected)
                                                            <input type="number"
                                                                step="0.01"
                                                                min="0.01"
                                                                wire:change="updateCriticalQty({{ $item['id'] }}, $event.target.value)"
                                                                value="{{ $selectedCriticalItems[$item['id']]['qty'] }}"
                                                                class="w-24 px-2 py-1 border border-gray-300 rounded text-sm text-right">
                                                            <span class="text-xs text-gray-500 ml-1">{{ $item['uom'] }}</span>
                                                        @else
                                                            <span class="text-sm text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        @if ($isSelected)
                                                            <input type="text"
                                                                wire:model="selectedCriticalItems.{{ $item['id'] }}.notes"
                                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
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
                                <h3 class="text-base font-bold text-gray-800">Daftar Item Tersedia (Non-Kritis)</h3>
                                <p class="text-sm text-gray-600">Pilih item tambahan yang ingin dipesan</p>
                            </div>

                            @if (count($this->availableItems) === 0)
                                <div class="text-center py-10 bg-gray-50 rounded-lg">
                                    <x-ui.sccr-icon name="inbox" :size="48" class="text-gray-400 mx-auto mb-3" />
                                    <p class="text-gray-600 font-medium">Tidak ada item tersedia</p>
                                    <p class="text-sm text-gray-500 mt-1">Semua item mungkin sudah dipilih atau tidak ada data</p>
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-50 border-b">
                                            <tr>
                                                <th class="px-3 py-3 text-left font-bold text-gray-700">Pilih</th>
                                                <th wire:click="sortBy('item_name')" class="px-3 py-3 text-left font-bold text-gray-700 cursor-pointer hover:bg-gray-100">
                                                    Item {!! $sortField === 'item_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                                </th>
                                                <th wire:click="sortBy('actual_stock')" class="px-3 py-3 text-center font-bold text-gray-700 cursor-pointer hover:bg-gray-100">
                                                    Stok Sekarang {!! $sortField === 'actual_stock' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                                </th>
                                                <th wire:click="sortBy('min_stock')" class="px-3 py-3 text-center font-bold text-gray-700 cursor-pointer hover:bg-gray-100">
                                                    Min Stok {!! $sortField === 'min_stock' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                                </th>
                                                <th wire:click="sortBy('selisih')" class="px-3 py-3 text-center font-bold text-gray-700 cursor-pointer hover:bg-gray-100">
                                                    Selisih {!! $sortField === 'selisih' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                                </th>
                                                <th class="px-3 py-3 text-center font-bold text-gray-700">Qty Order</th>
                                                <th class="px-3 py-3 text-left font-bold text-gray-700">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y bg-white">
                                            @foreach ($this->availableItems as $item)
                                                @php
                                                    $isSelected = collect($additionalItems)->contains(fn($i) => $i['id'] == $item['id']);
                                                    $selectedIndex = collect($additionalItems)->search(fn($i) => $i['id'] == $item['id']);
                                                @endphp
                                                <tr class="{{ $isSelected ? 'bg-blue-50' : '' }}">
                                                    <td class="px-3 py-3">
                                                        <input type="checkbox"
                                                            wire:click="toggleAdditionalItem({{ $item['id'] }})"
                                                            {{ $isSelected ? 'checked' : '' }}
                                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $item['name'] }}</div>
                                                        <div class="text-xs text-gray-500">{{ $item['sku'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono text-gray-900">
                                                            {{ number_format($item['actual_stock'], 2) }}
                                                        </span>
                                                        <div class="text-xs text-gray-500">{{ $item['uom'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono text-gray-900">
                                                            {{ number_format($item['min_stock'], 2) }}
                                                        </span>
                                                        <div class="text-xs text-gray-500">{{ $item['uom'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        <span class="text-sm font-mono @if($item['selisih'] > 0) text-red-600 font-semibold @else text-gray-500 @endif">
                                                            {{ number_format(abs($item['selisih']), 2) }}
                                                        </span>
                                                        <div class="text-xs text-gray-500">{{ $item['uom'] }}</div>
                                                    </td>
                                                    <td class="px-3 py-3 text-center">
                                                        @if ($isSelected)
                                                            <input type="number"
                                                                step="0.01"
                                                                min="0.01"
                                                                wire:change="updateAdditionalQty({{ $selectedIndex }}, $event.target.value)"
                                                                value="{{ $additionalItems[$selectedIndex]['qty'] }}"
                                                                class="w-24 px-2 py-1 border border-gray-300 rounded text-sm text-right">
                                                            <span class="text-xs text-gray-500 ml-1">{{ $item['uom'] }}</span>
                                                        @else
                                                            <span class="text-sm text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3">
                                                        @if ($isSelected)
                                                            <input type="text"
                                                                wire:model="additionalItems.{{ $selectedIndex }}.notes"
                                                                class="w-full px-2 py-1 border border-gray-300 rounded text-sm"
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
                    @endif
                </div>

                {{-- STEP 3: SUMMARY & ACTIONS --}}
                <div class="bg-white rounded-xl shadow border p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">Langkah 3: Ringkasan & Submit</h2>

                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">{{ count($selectedCriticalItems) }}</span> item stok kritis +
                                <span class="font-semibold">{{ count($additionalItems) }}</span> item tambahan
                            </p>
                        </div>

                        <div class="flex gap-3">
                            <a href="{{ route('dashboard.resto.purchase-request') }}"
                                class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                                Batal
                            </a>
                            <button type="button" wire:click="saveDraft"
                                class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-semibold">
                                Simpan Draft
                            </button>
                            <button type="button" wire:click="submitToRM"
                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                                Submit ke RM
                            </button>
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

</x-ui.sccr-card>
