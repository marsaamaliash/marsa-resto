<div x-data>
    <div class="relative px-8 py-6 bg-orange-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2 text-white">Kitchen</h1>
                <p class="text-lg text-orange-100">Kelola order dari waiters</p>
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
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white/80 backdrop-blur-sm">
            </div>
            
            <div class="flex gap-2 overflow-x-auto">
                <button wire:click="setFilter('all')" class="px-4 py-2 rounded-xl font-medium transition {{ $statusFilter === 'all' ? 'bg-orange-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Semua</button>
                <button wire:click="setFilter('waiting')" class="px-4 py-2 rounded-xl font-medium transition {{ $statusFilter === 'waiting' ? 'bg-orange-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Waiting</button>
                <button wire:click="setFilter('ready')" class="px-4 py-2 rounded-xl font-medium transition {{ $statusFilter === 'ready' ? 'bg-orange-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Ready</button>
                <button wire:click="setFilter('deliver')" class="px-4 py-2 rounded-xl font-medium transition {{ $statusFilter === 'deliver' ? 'bg-orange-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Deliver</button>
                <button wire:click="setFilter('reject')" class="px-4 py-2 rounded-xl font-medium transition {{ $statusFilter === 'reject' ? 'bg-orange-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Reject</button>
                <button wire:click="setFilter('failed')" class="px-4 py-2 rounded-xl font-medium transition {{ $statusFilter === 'failed' ? 'bg-orange-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50' }}">Gagal</button>
            </div>
        </div>

        @if ($statusFilter === 'failed')
            @if ($failedItems && $failedItems->isEmpty())
                <div class="text-center py-16 bg-white/60 backdrop-blur-sm rounded-2xl shadow">
                    <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 text-lg">Belum ada item gagal</p>
                </div>
            @else
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-orange-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Order</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Meja</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Menu</th>
                                <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Qty</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Alasan</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($failedItems as $item)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-800">{{ $item->order->order_number }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-800">{{ $item->order->table_number }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-800">{{ $item->menu->name }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="bg-orange-100 text-orange-700 font-bold px-2 py-1 rounded-lg">{{ $item->quantity }}x</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-red-600">{{ $item->reject_reason }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-gray-500">{{ $item->created_at->diffForHumans() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $failedItems->links() }}
                </div>
            @endif
        @elseif ($items->isEmpty())
            <div class="text-center py-16 bg-white/60 backdrop-blur-sm rounded-2xl shadow">
                <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500 text-lg">Belum ada order untuk filter ini</p>
            </div>
        @else
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-orange-100">
                        <tr>
                            <th wire:click="sortBy('order_created_at')" class="px-4 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer hover:bg-orange-200 select-none transition group">
                                <div class="flex items-center justify-between">
                                    <span>Urutan</span>
                                    <span class="text-orange-500">
                                        @if ($sortField === 'order_created_at') 
                                            {{ $sortDirection === 'asc' ? '↑' : '↓' }} 
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <th wire:click="sortBy('table_number')" class="px-4 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer hover:bg-orange-200 select-none transition group">
                                <div class="flex items-center justify-between">
                                    <span>Meja</span>
                                    <span class="text-orange-500">
                                        @if ($sortField === 'table_number') 
                                            {{ $sortDirection === 'asc' ? '↑' : '↑' }} 
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <th wire:click="sortBy('menu_name')" class="px-4 py-3 text-left text-sm font-semibold text-gray-700 cursor-pointer hover:bg-orange-200 select-none transition group">
                                <div class="flex items-center justify-between">
                                    <span>Menu</span>
                                    <span class="text-orange-500">
                                        @if ($sortField === 'menu_name') 
                                            {{ $sortDirection === 'asc' ? '↑' : '↓' }} 
                                        @endif
                                    </span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Qty</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($items as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-800">{{ $item->order->order_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->order->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $item->order->table_number }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-800">{{ $item->menu->name }}</div>
                                    @if ($item->notes)
                                        <div class="text-xs text-yellow-600 italic mt-0.5">{{ $item->notes }}</div>
                                    @endif
                                    @if ($item->reject_reason)
                                        <div class="text-xs text-red-600 font-medium mt-0.5">Ditolak: {{ $item->reject_reason }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="bg-orange-100 text-orange-700 font-bold px-2 py-1 rounded-lg">{{ $item->quantity }}x</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($item->status === 'waiting')
                                        <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">Waiting</span>
                                    @elseif ($item->status === 'ready')
                                        <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2.5 py-1 rounded-full">Ready</span>
                                    @elseif ($item->status === 'deliver')
                                        <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">Deliver</span>
                                    @elseif ($item->status === 'reject')
                                        <span class="bg-red-100 text-red-700 text-xs font-medium px-2.5 py-1 rounded-full">Reject</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($item->status === 'waiting')
                                        <button wire:click="updateItemStatus({{ $item->id }}, 'ready')"
                                            class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition shadow-sm">
                                            Ready
                                        </button>
                                        <button wire:click="openRejectModal({{ $item->id }})"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-lg transition shadow-sm ml-1">
                                            Tolak
                                        </button>
                                        <button wire:click="openFailedModal({{ $item->id }})"
                                            class="px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white text-xs font-medium rounded-lg transition shadow-sm ml-1">
                                            Gagal
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

    <div x-show="$wire.showRejectModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" x-on:click="$wire.showRejectModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Tolak Item</h3>
            <p class="text-sm text-gray-500 mb-4">Item akan dipindahkan ke daftar gagal masak dan status akan diubah menjadi reject.</p>
            <textarea wire:model.live="rejectReason" rows="3" placeholder="Masukkan alasan penolakan..."
                class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-red-400 focus:border-red-400 mb-4"></textarea>
            <div class="flex gap-3">
                <button type="button" wire:click="submitReject"
                    class="flex-1 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-colors">
                    Tolak
                </button>
                <button type="button" wire:click="$set('showRejectModal', false)"
                    class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                    Batal
                </button>
            </div>
        </div>
    </div>

    <div x-show="$wire.showFailedModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" x-on:click="$wire.showFailedModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Gagal Masak</h3>
            <p class="text-sm text-gray-500 mb-4">Item akan disimpan ke daftar gagal masak tanpa mengubah status.</p>
            <textarea wire:model.live="failedReason" rows="3" placeholder="Masukkan alasan gagal masak..."
                class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-gray-400 focus:border-gray-400 mb-4"></textarea>
            <div class="flex gap-3">
                <button type="button" wire:click="submitFailed"
                    class="flex-1 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-xl transition-colors">
                    Simpan
                </button>
                <button type="button" wire:click="$set('showFailedModal', false)"
                    class="flex-1 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                    Batal
                </button>
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