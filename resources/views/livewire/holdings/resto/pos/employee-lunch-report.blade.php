<div>
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Riwayat Makan Siang Karyawan</h1>
                <p class="text-lg text-gray-800">Daftar transaksi makan siang karyawan</p>
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
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow p-4">
                <p class="text-xs text-gray-500 font-medium">Transaksi Hari Ini</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ $todayCount }}</p>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow p-4">
                <p class="text-xs text-gray-500 font-medium">Total Hari Ini</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1">Rp {{ number_format($todayTotal, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow p-4">
                <p class="text-xs text-gray-500 font-medium">Jatah Terpakai</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rp {{ number_format($todayAllowanceUsed, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow p-4">
                <p class="text-xs text-gray-500 font-medium">Kelebihan Bayar</p>
                <p class="text-2xl font-bold text-orange-600 mt-1">Rp {{ number_format($todayExcess, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg p-5">
            <div class="flex flex-col sm:flex-row gap-3 mb-5">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nomor induk karyawan..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80">
                </div>
                <input wire:model.live="dateFilter" type="date"
                    class="px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80">
                <select wire:model.live="paymentFilter"
                    class="px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 bg-white/80">
                    <option value="">Semua Metode</option>
                    <option value="allowance">Jatah Harian</option>
                    <option value="salary_deduction">Potong Gaji</option>
                    <option value="QRIS">QRIS</option>
                </select>
                <button type="button" wire:click="resetFilters"
                    class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-sm font-medium transition-colors">
                    Reset
                </button>
            </div>

            @if ($transactions->isEmpty())
                <div class="text-center py-16">
                    <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 text-lg">Belum ada transaksi</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Waktu</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">No. Induk</th>
                                <th class="text-left py-3 px-4 font-semibold text-gray-600">Items</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-600">Total</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-600">Jatah</th>
                                <th class="text-right py-3 px-4 font-semibold text-gray-600">Kelebihan</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-600">Metode</th>
                                <th class="text-center py-3 px-4 font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $trx)
                                <tr class="border-b border-gray-100 hover:bg-yellow-50/50 transition-colors">
                                    <td class="py-3 px-4 text-gray-700 whitespace-nowrap">
                                        {{ $trx->paid_at?->format('d M Y') }}
                                        <span class="text-xs text-gray-400 block">{{ $trx->paid_at?->format('H:i') }}</span>
                                    </td>
                                    <td class="py-3 px-4 font-medium text-gray-800">{{ $trx->employee_number }}</td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ count($trx->items ?? []) }} item
                                    </td>
                                    <td class="py-3 px-4 text-right font-semibold text-gray-800">
                                        Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-green-600">
                                        Rp {{ number_format($trx->allowance_used, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if ($trx->excess_amount > 0)
                                            <span class="text-orange-600 font-medium">Rp {{ number_format($trx->excess_amount, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @php
                                            $methodLabel = match($trx->payment_method) {
                                                'allowance' => ['Jatah', 'bg-green-100 text-green-700'],
                                                'salary_deduction' => ['Potong Gaji', 'bg-blue-100 text-blue-700'],
                                                'QRIS' => ['QRIS', 'bg-yellow-100 text-yellow-700'],
                                                default => [$trx->payment_method, 'bg-gray-100 text-gray-700'],
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $methodLabel[1] }}">
                                            {{ $methodLabel[0] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" wire:click="openDetail({{ $trx->id }})"
                                            class="text-yellow-600 hover:text-yellow-700 font-medium text-xs transition-colors">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>

    @if ($showDetailModal && $selectedTransactionId)
        @php
            $detail = \App\Models\Holdings\Resto\Pos\Rst_EmployeeLunchTransaction::find($selectedTransactionId);
        @endphp
        @if ($detail)
            <div wire:click="showDetailModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50" wire:click="showDetailModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6" wire:click.stop>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Detail Transaksi</h3>
                        <button type="button" wire:click="$set('showDetailModal', false)"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Nomor Induk</span>
                            <span class="font-medium text-gray-800">{{ $detail->employee_number }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Waktu</span>
                            <span class="font-medium text-gray-800">{{ $detail->paid_at?->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Metode Pembayaran</span>
                            <span class="font-medium text-gray-800">{{ $detail->payment_method }}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3 mb-3">
                        <p class="text-sm font-semibold text-gray-700 mb-2">Item Pesanan</p>
                        <div class="space-y-2">
                            @foreach ($detail->items ?? [] as $item)
                                <div class="flex justify-between text-sm bg-gray-50 rounded-lg px-3 py-2">
                                    <div>
                                        <span class="text-gray-800 font-medium">{{ $item['name'] ?? '-' }}</span>
                                        <span class="text-gray-400 ml-1">x{{ $item['qty'] ?? 1 }}</span>
                                        @if (!empty($item['note']))
                                            <p class="text-xs text-yellow-600 italic">{{ $item['note'] }}</p>
                                        @endif
                                    </div>
                                    <span class="text-gray-700">Rp {{ number_format(($item['subtotal'] ?? 0), 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Total</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($detail->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Jatah Digunakan</span>
                            <span class="font-medium text-green-600">Rp {{ number_format($detail->allowance_used, 0, ',', '.') }}</span>
                        </div>
                        @if ($detail->excess_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Kelebihan</span>
                                <span class="font-medium text-orange-600">Rp {{ number_format($detail->excess_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
