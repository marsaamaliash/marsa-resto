<div x-data="{ statusFilter: @entangle('statusFilter') }">
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Order Saya</h1>
                <p class="text-lg text-gray-800">Lihat status order yang sudah dipesan</p>
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
            <button @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Semua</button>
            <button @click="statusFilter = 'waiting'" :class="statusFilter === 'waiting' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Waiting</button>
            <button @click="statusFilter = 'ready'" :class="statusFilter === 'ready' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Ready</button>
            <button @click="statusFilter = 'deliver'" :class="statusFilter === 'deliver' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Deliver</button>
            <button @click="statusFilter = 'reject'" :class="statusFilter === 'reject' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-xl font-medium transition">Reject</button>
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
                                    Meja {{ $order->table_number ?? '-' }} - {{ $order->customer_name }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="text-right">
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                                    @switch($order->status)
                                        @case('waiting') bg-yellow-100 text-yellow-700 @break
                                        @case('ready') bg-blue-100 text-blue-700 @break
                                        @case('deliver') bg-green-100 text-green-700 @break
                                        @case('reject') bg-red-100 text-red-700 @break
                                    @endswitch">
                                    @switch($order->status)
                                        @case('waiting') Menunggu @break
                                        @case('ready') Siap @break
                                        @case('deliver') Diantar @break
                                        @case('reject') Ditolak @break
                                    @endswitch
                                </span>
                                <p class="text-lg font-bold text-yellow-600 mt-2">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="space-y-2 mb-4">
                            @foreach ($order->items as $item)
                                <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="bg-yellow-100 text-yellow-700 font-bold px-2 py-1 rounded-lg">{{ $item->quantity }}x</span>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $item->menu->name }}</p>
                                            @if ($item->notes)
                                                <p class="text-xs text-gray-500">{{ $item->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium
                                        @switch($item->status)
                                            @case('waiting') text-yellow-600 @break
                                            @case('ready') text-blue-600 @break
                                            @case('deliver') text-green-600 @break
                                            @case('reject') text-red-600 @break
                                        @endswitch">
                                        @switch($item->status)
                                            @case('waiting') Waiting @break
                                            @case('ready') Ready @break
                                            @case('deliver') Delivered @break
                                            @case('reject') Reject @break
                                        @endswitch
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        @if ($order->status === 'deliver')
                            <div class="flex gap-2">
                                <button wire:click="markDelivered({{ $order->id }})"
                                    class="flex-1 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-xl transition">
                                    Tandai Selesai
                                </button>
                            </div>
                        @endif
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