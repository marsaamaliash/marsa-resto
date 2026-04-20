<x-ui.sccr-card transparent wire:key="purchase-order-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-b-3xl shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Daftar Purchase Order</h1>
                <p class="text-blue-100 text-sm mt-1">Kelola semua Purchase Order (PO) dengan approval flow</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">Total: <span class="font-bold text-black">{{ $pos->total() ?? 0 }}</span></div>
        </div>
    </div>

    {{-- ================= FILTERS & SEARCH ================= --}}
    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-2">

            {{-- SEARCH & FILTERS --}}
            <div class="flex flex-wrap items-center gap-2 flex-grow">

                {{-- SEARCH INPUT --}}
                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Cari</span>
                    <input type="text" wire:model.live="search"
                        placeholder="PO Number, Vendor..."
                        class="pl-3 pr-3 py-2 border border-gray-300 rounded-lg text-sm w-64 focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- STATUS FILTER --}}
                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <select wire:model.live="statusFilter"
                        class="pl-3 pr-3 py-2 border border-gray-300 rounded-lg text-sm w-48 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Semua Status --</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- LOCATION FILTER --}}
                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Lokasi</span>
                    <select wire:model.live="selectedLocationId"
                        class="pl-3 pr-3 py-2 border border-gray-300 rounded-lg text-sm w-48 focus:ring-2 focus:ring-blue-500">
                        @foreach ($locations as $locId => $locName)
                            <option value="{{ $locId }}">{{ $locName }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            {{-- CREATE BUTTON --}}
            <div class="flex gap-2">
                <a href="{{ route('dashboard.resto.purchase-order.create') }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat PO
                </a>
            </div>

        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2 overflow-hidden flex flex-col">
        <div class="flex-1 min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            {{-- TABLE SCROLLER --}}
            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">PO Number</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">PR Number</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Vendor</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Total Amount</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Payment By</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Created</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($pos as $po)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $po->po_number }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $po->purchaseRequest?->pr_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $po->vendor_name }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 rounded text-white text-xs font-bold
                                        {{ $po->status === 'draft' ? 'bg-gray-500' : '' }}
                                        {{ $po->status === 'pending_rm' ? 'bg-yellow-500' : '' }}
                                        {{ $po->status === 'pending_spv' ? 'bg-blue-500' : '' }}
                                        {{ $po->status === 'approved' ? 'bg-green-500' : '' }}
                                        {{ $po->status === 'rejected' ? 'bg-red-500' : '' }}
                                        {{ $po->status === 'revised' ? 'bg-orange-500' : '' }}">
                                        {{ $statuses[$po->status] ?? ucfirst($po->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-700">
                                    Rp {{ number_format($po->total_amount ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-700">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        {{ $po->payment_by === 'holding' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst($po->payment_by) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600">
                                    {{ $po->created_at?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('dashboard.resto.purchase-order.detail', ['id' => $po->id]) }}"
                                            class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
                                            Detail
                                        </a>
                                        @if ($po->canBeEdited())
                                            <button wire:click="deletePO({{ $po->id }})"
                                                class="px-3 py-1 bg-red-600 text-white rounded text-xs hover:bg-red-700">
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="font-medium">Tidak ada Purchase Order</p>
                                    <p class="text-xs mt-1">Mulai dengan membuat PO dari PR yang diapprove</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="bg-gray-50 border-t px-4 py-3">
                {{ $pos->links() }}
            </div>

        </div>
    </div>

</x-ui.sccr-card>
