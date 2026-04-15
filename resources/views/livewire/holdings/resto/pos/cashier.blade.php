<div x-data wire:poll.5s.keep="poll">
    <div x-show="$wire.isPolling" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-50">
        <div class="flex items-center gap-2 bg-white/90 backdrop-blur px-3 py-1.5 rounded-full shadow text-xs text-gray-500">
            <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            Memperbarui...
        </div>
    </div>

    <div class="relative px-8 py-6 bg-emerald-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2 text-white">Kasir</h1>
                <p class="text-lg text-emerald-100">Konfirmasi pembayaran QRIS</p>
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
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari order atau pelanggan..."
                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-white/80 backdrop-blur-sm">
            </div>

            <select wire:model.live="tableFilter"
                class="px-4 py-2 rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 bg-white/80 backdrop-blur-sm min-w-[180px]">
                <option value="">Semua Meja</option>
                @foreach ($tables as $table)
                    <option value="{{ $table->table_number }}">Meja {{ $table->table_number }}</option>
                @endforeach
            </select>

            <div class="flex gap-2 overflow-x-auto">
                <button wire:click="setFilter('unpaid')" class="px-4 py-2 rounded-xl font-medium transition {{ $statusFilter === 'unpaid' ? 'bg-emerald-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Belum Bayar
                </button>
                <button wire:click="setFilter('paid')" class="px-4 py-2 rounded-xl font-medium transition {{ $statusFilter === 'paid' ? 'bg-emerald-600 text-white shadow' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Selesai
                </button>
            </div>
        </div>

        @if ($orders->isEmpty())
            <div class="text-center py-16 bg-white/60 backdrop-blur-sm rounded-2xl shadow">
                <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m6 9l2 2 4-4"/>
                </svg>
                <p class="text-gray-500 text-lg">Belum ada order</p>
            </div>
        @else
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-emerald-100">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Order</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Meja</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Pelanggan</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Items</th>
                            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Subtotal</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-800">{{ $order->order_number }}</div>
                                    <div class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $order->table_number ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-700">{{ $order->customer_name ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $activeItems = $order->items->filter(fn($i) => $i->status !== 'reject');
                                    @endphp
                                    <span class="bg-emerald-100 text-emerald-700 font-bold px-2 py-1 rounded-lg">{{ $activeItems->count() }} item</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @php
                                        $subtotal = $activeItems->sum(fn($i) => $i->quantity * $i->unit_price);
                                    @endphp
                                    <span class="font-semibold text-gray-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($order->payment_status === 'paid')
                                        <span class="bg-green-100 text-green-700 text-xs font-medium px-2.5 py-1 rounded-full">Selesai</span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-700 text-xs font-medium px-2.5 py-1 rounded-full">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($order->payment_status !== 'paid')
                                        <button wire:click="openOrderDetail({{ $order->id }})"
                                            class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium rounded-lg transition shadow-sm">
                                            Bayar
                                        </button>
                                    @else
                                        <button wire:click="openReceipt({{ $order->id }})"
                                            class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-lg transition shadow-sm">
                                            Struk
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <div x-show="$wire.showOrderModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" x-on:click="$wire.showOrderModal = false"></div>
        @if ($selectedOrderId)
            @php
                $detailOrder = \App\Models\Holdings\Resto\Pos\Rst_Order::with('items.menu')->find($selectedOrderId);
                $detailTotals = $detailOrder ? $this->getOrderTotals($selectedOrderId) : null;
            @endphp
            @if ($detailOrder && $detailTotals)
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Detail Order</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ $detailOrder->order_number }} - Meja {{ $detailOrder->table_number ?: '-' }}</p>

                    <div class="space-y-2 mb-4 max-h-60 overflow-y-auto">
                        @foreach ($detailOrder->items->filter(fn($i) => $i->status !== 'reject') as $item)
                            <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                                <div>
                                    <span class="font-medium text-gray-800">{{ $item->menu->name ?? 'Menu' }}</span>
                                    <span class="text-gray-400 ml-1">x{{ $item->quantity }}</span>
                                    @if ($item->notes)
                                        <div class="text-xs text-yellow-600 italic">{{ $item->notes }}</div>
                                    @endif
                                </div>
                                <span class="text-gray-700">Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-200 pt-3 space-y-1">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($detailTotals['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>PPN (10%)</span>
                            <span>Rp {{ number_format($detailTotals['tax'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Service (5%)</span>
                            <span>Rp {{ number_format($detailTotals['service'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-gray-800 pt-2 border-t border-gray-200">
                            <span>Total</span>
                            <span>Rp {{ number_format($detailTotals['total'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" wire:click="openPaymentModal"
                            class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-xl transition-colors">
                            Bayar QRIS
                        </button>
                        <button type="button" x-on:click="$wire.showOrderModal = false"
                            class="flex-1 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                            Batal
                        </button>
                    </div>
                </div>
            @endif
        @endif
    </div>

    <div x-show="$wire.showPaymentModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" x-on:click="$wire.showPaymentModal = false"></div>
        @if ($selectedOrderId)
            @php
                $payTotals = $this->getOrderTotals($selectedOrderId);
            @endphp
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10" x-data="{ checked: false }">
                <div class="text-center mb-4">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Pembayaran QRIS</h3>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($payTotals['total'], 0, ',', '.') }}</p>
                </div>

                <div class="bg-gray-50 rounded-xl p-3 mb-4 text-sm text-gray-600 text-center">
                    Scan QRIS untuk melakukan pembayaran
                </div>

                <label class="flex items-center gap-2 mb-4 cursor-pointer">
                    <input type="checkbox" x-model="checked" class="w-5 h-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Saya sudah terima pembayaran QRIS</span>
                </label>

                <div class="flex gap-3">
                    <button type="button" wire:click="processPayment"
                        class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-medium rounded-xl transition-colors"
                        :disabled="!checked"
                        :class="{ 'opacity-50 cursor-not-allowed': !checked }">
                        Konfirmasi Bayar
                    </button>
                    <button type="button" x-on:click="$wire.showPaymentModal = false"
                        class="flex-1 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        @endif
    </div>

    <div x-show="$wire.showReceiptModal" x-transition.opacity.duration.300ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-black/50" x-on:click="$wire.showReceiptModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
            <h3 class="text-lg font-bold text-gray-800 mb-4 text-center">Struk Pembayaran</h3>
            @if ($receiptOrderId)
                @php
                    $receiptData = $this->getReceiptData($receiptOrderId);
                    $rOrder = $receiptData['order'];
                    $rTotals = $receiptData['totals'];
                    $rPayment = $receiptData['payment'];
                @endphp
                <div class="border border-gray-200 rounded-xl p-4 text-sm space-y-3" id="receipt-content">
                    <div class="text-center border-b border-dashed border-gray-300 pb-2">
                        <p class="font-bold text-base">RESTO SCCR</p>
                        <p class="text-xs text-gray-500">{{ $rOrder->order_number }}</p>
                        <p class="text-xs text-gray-400">{{ $rOrder->created_at->format('d M Y, H:i') }}</p>
                        <p class="text-xs text-gray-500">Meja: {{ $rOrder->table_number ?: '-' }}</p>
                    </div>
                    <div class="space-y-1">
                        @foreach ($rOrder->items->filter(fn($i) => $i->status !== 'reject') as $item)
                            <div class="flex justify-between text-xs">
                                <span>{{ $item->menu->name ?? 'Menu' }} x{{ $item->quantity }}</span>
                                <span>Rp {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-dashed border-gray-300 pt-2 space-y-1">
                        <div class="flex justify-between text-xs">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($rTotals['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>PPN (10%)</span>
                            <span>Rp {{ number_format($rTotals['tax'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span>Service (5%)</span>
                            <span>Rp {{ number_format($rTotals['service'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold border-t border-gray-300 pt-1">
                            <span>TOTAL</span>
                            <span>Rp {{ number_format($rTotals['total'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="text-center border-t border-dashed border-gray-300 pt-2 text-xs text-gray-500">
                        <p>Pembayaran: QRIS</p>
                        <p>{{ $rPayment->paid_at->format('d M Y, H:i') }}</p>
                        <p class="font-medium mt-1">Terima Kasih</p>
                    </div>
                </div>
            @endif

            <div class="flex gap-3 mt-4">
                <button type="button" wire:click="printReceipt"
                    class="flex-1 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-xl transition-colors">
                    Cetak
                </button>
                <button type="button" x-on:click="$wire.showReceiptModal = false"
                    class="flex-1 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-xl transition-colors">
                    Tutup
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