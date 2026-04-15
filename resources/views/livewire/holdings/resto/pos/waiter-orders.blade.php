<div x-data>
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2 text-white">Order Saya</h1>
                <p class="text-lg text-yellow-100">Lihat dan kelola status order</p>
            </div>
        </div>
        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="fixed top-0 left-0 w-full h-full z-0 pointer-events-none">
        <img src="{{ asset('images/bg-gedung.jpg') }}" alt="Background" class="w-full h-full object-cover opacity-30">
    </div>

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 relative z-10">
        <div class="flex flex-wrap gap-2 mb-6">
            <div class="relative flex-1 min-w-[200px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari menu..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80 backdrop-blur-sm">
            </div>

            <select wire:model.live="mejaFilter"
                class="px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80 backdrop-blur-sm min-w-[200px]">
                <option value="">Semua Meja</option>
                @foreach ($availableOrders as $order)
                    <option value="{{ $order->id }}">Meja {{ $order->table_number }} - {{ $order->customer_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="statusFilter"
                class="px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80 backdrop-blur-sm min-w-[160px]">
                @foreach ($statusFilters as $filter)
                    <option value="{{ $filter }}">{{ $filter === 'all' ? 'Semua Status' : ucfirst($filter) }}</option>
                @endforeach
            </select>

            <button wire:click="openTambahModal"
                class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-xl transition shadow-sm whitespace-nowrap">
                + Tambah Order
            </button>

            <button wire:click="openEditModal"
                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-xl transition shadow-sm whitespace-nowrap">
                Edit Info
            </button>
        </div>

        @if ($items->isEmpty())
            <div class="text-center py-16 bg-white/60 backdrop-blur-sm rounded-2xl shadow">
                <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500 text-lg">Belum ada item order</p>
            </div>
        @else
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-yellow-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Meja</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Pelanggan</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Menu</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Qty</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($items as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $item->order->table_number ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-700">{{ $item->order->customer_name }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-800">{{ $item->menu->name }}</div>
                                    @if ($item->notes)
                                        <div class="text-xs text-yellow-600 italic mt-0.5">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-yellow-100 text-yellow-700 font-bold px-2 py-1 rounded-lg">{{ $item->quantity }}x</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($item->status === 'waiting')
                                        <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">Waiting</span>
                                    @elseif ($item->status === 'ready')
                                        <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">Ready</span>
                                    @elseif ($item->status === 'deliver')
                                        <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">Delivered</span>
                                    @elseif ($item->status === 'reject')
                                        <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">Reject</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($item->status === 'ready')
                                        <button wire:click="deliverItem({{ $item->id }})"
                                            class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition shadow-sm">
                                            Antar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <div x-show="$wire.showTambahModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" x-on:click="$wire.showTambahModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Tambah Item ke Order</h3>
            <p class="text-sm text-gray-500 mb-4">Pilih order yang ingin ditambahkan item</p>

            @if ($availableOrders->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <p class="text-sm">Tidak ada order aktif</p>
                </div>
            @else
                <div class="space-y-2 max-h-80 overflow-y-auto pr-1 mb-4">
                    @foreach ($availableOrders as $order)
                        <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 cursor-pointer transition hover:bg-yellow-50 {{ $selectTambahOrderId === $order->id ? 'ring-2 ring-yellow-400 bg-yellow-50' : '' }}"
                            wire:click="selectTambahOrder({{ $order->id }})">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Meja {{ $order->table_number ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $order->customer_name }}</p>
                                <p class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            @if ($selectTambahOrderId === $order->id)
                                <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="flex gap-3">
                <button type="button" wire:click="submitTambahOrder"
                    class="flex-1 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-xl transition-colors">
                    Pilih & Lanjut
                </button>
                <button type="button" x-on:click="$wire.showTambahModal = false"
                    class="flex-1 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <div x-show="$wire.showEditModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" x-on:click="$wire.showEditModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Edit Info Order</h3>
            <p class="text-sm text-gray-500 mb-4">Pilih order yang ingin diedit informasinya</p>

            @if ($availableOrders->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <p class="text-sm">Tidak ada order aktif</p>
                </div>
            @else
                <div class="space-y-2 max-h-80 overflow-y-auto pr-1 mb-4">
                    @if (! $selectEditOrderId)
                        @foreach ($availableOrders as $order)
                            <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3 cursor-pointer transition hover:bg-blue-50"
                                wire:click="selectEditOrder({{ $order->id }})">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Meja {{ $order->table_number ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->customer_name }}</p>
                                </div>
                                <span class="text-xs text-blue-500 font-medium">Edit</span>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-blue-50 rounded-xl px-4 py-3 mb-3">
                            <p class="text-sm font-semibold text-gray-800">Meja {{ $availableOrders->firstWhere('id', $selectEditOrderId)?->table_number ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $availableOrders->firstWhere('id', $selectEditOrderId)?->customer_name }}</p>
                        </div>

                        <div class="space-y-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelanggan</label>
                                <input wire:model.live="editCustomerName" type="text" placeholder="Nama pelanggan..."
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Meja</label>
                                <input wire:model.live="editTableNumber" type="number" placeholder="Nomor meja..."
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex gap-3">
                @if ($selectEditOrderId)
                    <button type="button" wire:click="submitEditOrder"
                        class="flex-1 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-xl transition-colors">
                        Simpan
                    </button>
                    <button type="button" wire:click="$set('selectEditOrderId', null)"
                        class="flex-1 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                        Kembali
                    </button>
                @else
                    <button type="button" x-on:click="$wire.showEditModal = false"
                        class="flex-1 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                        Batal
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div x-show="$wire.toastShow" x-transition.opacity.duration.300ms
        class="fixed top-[80px] right-6 z-50 px-5 py-3 rounded-xl shadow-lg flex items-center gap-3"
        :class="$wire.toastType === 'success' ? 'bg-green-500' : 'bg-red-500'"
        style="display: none;"
        x-init="$watch('$wire.toastShow', value => { if(value) setTimeout(() => $wire.toastShow = false, 3000) })">
        
        <span class="text-white font-medium" x-text="$wire.toastMessage"></span>
        
        <button @click="$wire.toastShow = false" class="text-white/80 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
