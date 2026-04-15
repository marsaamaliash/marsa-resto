<div x-data="{ statusFilter: @entangle('statusFilter') }">
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

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">
        <div class="flex gap-2 mb-6">
            <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Semua</button>
            <button @click="statusFilter = 'pending'" :class="statusFilter === 'pending' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Pending</button>
            <button @click="statusFilter = 'confirmed'" :class="statusFilter === 'confirmed' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Confirmed</button>
            <button @click="statusFilter = 'processing'" :class="statusFilter === 'processing' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Processing</button>
            <button @click="statusFilter = 'ready'" :class="statusFilter === 'ready' ? 'bg-orange-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Ready</button>
        </div>

        @if ($orders->isEmpty())
            <div class="text-center py-16 bg-white/60 backdrop-blur-sm rounded-2xl shadow">
                <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-500 text-lg">Belum ada order</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach ($orders as $order)
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-gray-800">{{ $order->order_number }}</h3>
                                <p class="text-sm text-gray-600">
                                    {{ $order->customer_name }} - Meja {{ $order->table_number ?? '-' }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                                    @switch($order->status)
                                        @case('pending') bg-yellow-100 text-yellow-700 @break
                                        @case('confirmed') bg-blue-100 text-blue-700 @break
                                        @case('processing') bg-orange-100 text-orange-700 @break
                                        @case('ready') bg-green-100 text-green-700 @break
                                    @endswitch">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <p class="text-lg font-bold text-orange-600 mt-2">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 mb-4">
                            @foreach ($order->items as $item)
                                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="bg-orange-100 text-orange-700 font-bold px-2 py-1 rounded-lg">{{ $item->quantity }}x</span>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $item->menu->name }}</p>
                                            @if ($item->notes)
                                                <p class="text-xs text-gray-500">{{ $item->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <select wire:change="updateItemStatus({{ $item->id }}, $event.target.value)"
                                            class="text-sm rounded-lg border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-orange-400">
                                            <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ $item->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="processing" {{ $item->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                            <option value="ready" {{ $item->status === 'ready' ? 'selected' : '' }}>Ready</option>
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex gap-2">
                            @if (in_array($order->status, ['pending', 'confirmed']))
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'processing')"
                                    class="flex-1 py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-xl transition">
                                    Proses
                                </button>
                            @endif
                            @if ($order->status === 'processing')
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'ready')"
                                    class="flex-1 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-xl transition">
                                    Selesai
                                </button>
                            @endif
                            @if ($order->status === 'ready')
                                <button wire:click="updateOrderStatus({{ $order->id }}, 'served')"
                                    class="flex-1 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-xl transition">
                                    Served
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <div x-show="$toastShow" x-transition.opacity.duration.300ms
        class="fixed bottom-6 right-6 z-50 px-5 py-3 rounded-xl shadow-lg"
        :class="$toastType === 'success' ? 'bg-green-500' : 'bg-red-500'"
        style="display: none;">
        <div class="flex items-center gap-3">
            <span class="text-white font-medium" x-text="$toastMessage"></span>
            <button @click="$wire.hideToast()" class="text-white/80 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
</div>