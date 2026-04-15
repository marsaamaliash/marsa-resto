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
            </div>
        </div>

        @if ($items->isEmpty())
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
                                        <div class="text-xs text-yellow-600 italic mt-0.5">"{{ $item->notes }}"</div>
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
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($item->status === 'waiting')
                                        <button wire:click="updateItemStatus({{ $item->id }}, 'ready')"
                                            class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition shadow-sm">
                                            Ready
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