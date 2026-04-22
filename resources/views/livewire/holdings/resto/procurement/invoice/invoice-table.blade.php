<x-ui.sccr-card transparent wire:key="invoice-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Invoice</h1>
                <p class="text-blue-100 text-sm">
                    Kelola invoice dan pembayaran Purchase Order
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $data->total() }}</span> data
            </div>
        </div>
    </div>

    {{-- ================= FILTERS & ACTIONS ================= --}}
    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-1">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-1 flex-grow">

                {{-- SEARCH INPUT --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Cari
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search"
                        placeholder="PO Number, Invoice Number, Vendor..." class="w-72" />
                </div>

                {{-- FILTER 1: Payment Status --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Payment Status
                    </span>
                    <x-ui.sccr-select name="filterPaymentStatus" wire:model.live="filterPaymentStatus" :options="$this->filterPaymentStatusOptions" class="w-40" />
                </div>

                {{-- FILTER 2: Payment By --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Payment By
                    </span>
                    <x-ui.sccr-select name="filterPaymentBy" wire:model.live="filterPaymentBy" :options="$this->filterPaymentByOptions" class="w-40" />
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-wrap items-center gap-1">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="bg-gray-900 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="cari" :size="20" />
                        Cari
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="clearFilters"
                        class="bg-gray-800 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" />
                        Clear
                    </x-ui.sccr-button>
                </div>
            </form>

            {{-- Right: perpage & export --}}
            <div class="flex items-end gap-1 ml-auto">
                @if ($canExport)
                    <x-ui.sccr-button type="button" wire:click="exportExcel"
                        class="bg-green-600 text-white hover:bg-green-700">
                        <x-ui.sccr-icon name="file-excel" :size="18" />
                        Export Excel
                    </x-ui.sccr-button>
                @endif

                <div class="relative top-0">
                    <span class="absolute -top-4 left-1 text-[10px] font-bold text-black uppercase">
                        Show
                    </span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TABLE (SCROLL AREA) ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            {{-- TABLE SCROLLER --}}
            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-900">
                    <thead class="bg-gray-700/80 text-white sticky top-0 z-10">
                        <tr>
                            {{-- PO Number --}}
                            <th wire:click="sortBy('po_number')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                PO Number {!! $sortField === 'po_number' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Vendor --}}
                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Vendor
                            </th>

                            {{-- Location --}}
                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Lokasi
                            </th>

                            {{-- Total Amount --}}
                            <th wire:click="sortBy('total_amount')"
                                class="px-3 py-3 text-right text-xs font-bold cursor-pointer">
                                Total Amount {!! $sortField === 'total_amount' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Payment By --}}
                            <th wire:click="sortBy('payment_by')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Payment By {!! $sortField === 'payment_by' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Payment Status --}}
                            <th wire:click="sortBy('payment_status')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Payment Status {!! $sortField === 'payment_status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Invoice Number --}}
                            <th wire:click="sortBy('invoice_number')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Invoice Number {!! $sortField === 'invoice_number' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Invoice Date --}}
                            <th wire:click="sortBy('invoice_date')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Invoice Date {!! $sortField === 'invoice_date' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Actions --}}
                            <th class="px-4 py-3 text-center text-xs font-bold">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition">
                                {{-- PO Number --}}
                                <td class="px-3 py-2 font-mono text-sm font-semibold text-blue-700">
                                    {{ $item['po_number'] ?? '-' }}
                                </td>

                                {{-- Vendor --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item['vendor_name'] ?? '-' }}
                                </td>

                                {{-- Location --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item->location?->name ?? '-' }}
                                </td>

                                {{-- Total Amount --}}
                                <td class="px-3 py-2 text-right text-sm font-semibold">
                                    Rp {{ number_format($item['total_amount'] ?? 0, 2, ',', '.') }}
                                </td>

                                {{-- Payment By --}}
                                <td class="px-3 py-2 text-center">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold
                                        {{ $item['payment_by'] === 'holding' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst($item['payment_by'] ?? '-') }}
                                    </span>
                                </td>

                                {{-- Payment Status --}}
                                <td class="px-3 py-2 text-center">
                                    @php
                                        $paymentStatusColor = match($item['payment_status']) {
                                            'unpaid' => 'bg-red-100 text-red-800',
                                            'pending_finance' => 'bg-yellow-100 text-yellow-800',
                                            'paid' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $paymentStatusLabel = match($item['payment_status']) {
                                            'unpaid' => 'Unpaid',
                                            'pending_finance' => 'Pending Finance',
                                            'paid' => 'Paid',
                                            default => ucfirst($item['payment_status']),
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-xs {{ $paymentStatusColor }}">
                                        {{ $paymentStatusLabel }}
                                    </span>
                                </td>

                                {{-- Invoice Number --}}
                                <td class="px-3 py-2 text-sm font-mono">
                                    {{ $item['invoice_number'] ?? '-' }}
                                </td>

                                {{-- Invoice Date --}}
                                <td class="px-3 py-2 text-sm text-gray-600">
                                    {{ $item->invoice_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                {{-- ROW ACTIONS --}}
                                <td class="px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        {{-- View Detail --}}
                                        <a href="{{ route('dashboard.resto.invoice.detail', $item['id']) }}"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="18" />
                                        </a>

                                        {{-- Mark as Paid --}}
                                        @if ($item['payment_status'] === 'pending_finance' && $canMarkPaid)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="markAsPaid({{ $item['id'] }})"
                                                class="text-green-600 hover:scale-125" title="Mark as Paid">
                                                <x-ui.sccr-icon name="check" :size="18" />
                                            </x-ui.sccr-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-gray-400 italic">
                                    Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MODULE FOOTER (pagination) --}}
            <div
                class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600">
                    Total invoice
                </div>

                <div>
                    {{ $data->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

</x-ui.sccr-card>
