<x-ui.sccr-card transparent wire:key="purchase-request-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Purchase Request</h1>
                <p class="text-blue-100 text-sm">
                    Pengajuan pembelian barang (PR) dengan multi-level approval
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
                        placeholder="PR Number, Lokasi, Item..." class="w-72" />
                </div>

                {{-- FILTER 1: Status --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Status
                    </span>
                    <x-ui.sccr-select name="filterStatus" wire:model.live="filterStatus" :options="$this->filterStatusOptions" class="w-40" />
                </div>

                {{-- FILTER 2: Location --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Lokasi
                    </span>
                    <x-ui.sccr-select name="filterLocation" wire:model.live="filterLocation" :options="$this->filterLocationOptions" class="w-48" />
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
                            {{-- SELECT ALL CHECKBOX --}}
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            {{-- PR Number --}}
                            <th wire:click="sortBy('pr_number')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                PR Number {!! $sortField === 'pr_number' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Request Date --}}
                            <th wire:click="sortBy('requested_at')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Tanggal {!! $sortField === 'requested_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Location --}}
                            <th wire:click="sortBy('requester_location_id')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Lokasi {!! $sortField === 'requester_location_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Requester --}}
                            <th wire:click="sortBy('requested_by')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Requester {!! $sortField === 'requested_by' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Status --}}
                            <th wire:click="sortBy('status')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Total Items --}}
                            <th class="px-3 py-3 text-center text-xs font-bold">
                                Items
                            </th>

                            {{-- Total Cost --}}
                            <th wire:click="sortBy('total_estimated_cost')"
                                class="px-3 py-3 text-right text-xs font-bold cursor-pointer">
                                Total {!! $sortField === 'total_estimated_cost' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Actions --}}
                            <th class="px-4 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Aksi</span>

                                    @if ($canCreate)
                                        <x-ui.sccr-button type="button" variant="icon-circle" wire:click="openCreateFromCritical"
                                            class="w-8 h-8 hover:scale-105" title="Buat PR dari Stok Kritis">
                                            <x-ui.sccr-icon name="plus" :size="18" />
                                        </x-ui.sccr-button>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-2 py-2 text-center">
                                    <input type="checkbox" value="{{ $item['id'] }}" wire:model.live="selectedItems"
                                        class="rounded border-gray-300">
                                </td>

                                {{-- PR Number --}}
                                <td class="px-3 py-2 font-mono text-sm font-semibold text-blue-700">
                                    {{ $item['pr_number'] ?? '-' }}
                                </td>

                                {{-- Request Date --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item['requested_at'] ? \Carbon\Carbon::parse($item['requested_at'])->format('d/m/Y H:i') : '-' }}
                                </td>

                                {{-- Location --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item->requesterLocation?->name ?? '-' }}
                                </td>

                                {{-- Requester --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item['requested_by'] ?? '-' }}
                                </td>

                                {{-- Status --}}
                                <td class="px-3 py-2 text-center">
                                    @php
                                        $statusColor = match($item['status']) {
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'pending_rm' => 'bg-yellow-100 text-yellow-800',
                                            'pending_spv' => 'bg-blue-100 text-blue-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'revised' => 'bg-orange-100 text-orange-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        $statusLabel = match($item['status']) {
                                            'draft' => 'Draft',
                                            'pending_rm' => 'Pending RM',
                                            'pending_spv' => 'Pending SPV',
                                            'approved' => 'Approved',
                                            'rejected' => 'Rejected',
                                            'revised' => 'Revised',
                                            default => ucfirst($item['status']),
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-xs {{ $statusColor }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                {{-- Total Items --}}
                                <td class="px-3 py-2 text-center text-sm">
                                    <span class="font-semibold">{{ $item->items->count() }}</span>
                                    @if ($item->items->where('is_critical', true)->count() > 0)
                                        <span class="text-red-600 text-xs" title="Stok Kritis">
                                            ({{ $item->items->where('is_critical', true)->count() }} critical)
                                        </span>
                                    @endif
                                </td>

                                {{-- Total Cost --}}
                                <td class="px-3 py-2 text-right text-sm font-mono">
                                    {{ number_format($item['total_estimated_cost'] ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- ROW ACTIONS --}}
                                <td class="px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        {{-- View Detail --}}
                                        <a href="{{ route('dashboard.resto.purchase-request.detail', $item['id']) }}"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="18" />
                                        </a>

                                        {{-- Edit/Revise --}}
                                        @if ($item->canBeEdited() && ($canCreate || $canUpdate))
                                            <a href="{{ route('dashboard.resto.purchase-request.detail', $item['id']) }}?mode=edit"
                                                class="text-blue-600 hover:scale-125" title="Edit">
                                                <x-ui.sccr-icon name="edit" :size="18" />
                                            </a>
                                        @endif

                                        {{-- RM Approve --}}
                                        @if ($item['status'] === 'pending_rm' && $canApproveRM)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="directApproveByRM({{ $item['id'] }})"
                                                class="text-green-600 hover:scale-125" title="Approve (RM)">
                                                <x-ui.sccr-icon name="approve" :size="18" />
                                            </x-ui.sccr-button>
                                        @endif

                                        {{-- SPV Approve --}}
                                        @if ($item['status'] === 'pending_spv' && $canApproveSPV)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="directApproveBySPV({{ $item['id'] }})"
                                                class="text-green-600 hover:scale-125" title="Approve (SPV)">
                                                <x-ui.sccr-icon name="approve" :size="18" />
                                            </x-ui.sccr-button>
                                        @endif

                                        {{-- Reject --}}
                                        @if (in_array($item['status'], ['pending_rm', 'pending_spv']) && ($canApproveRM || $canApproveSPV))
                                            @php
                                                $rejectLevel = $item['status'] === 'pending_rm' ? 1 : 2;
                                            @endphp
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openActionOverlay('reject', '{{ $item['id'] }}', {{ $rejectLevel }})"
                                                class="text-red-600 hover:scale-125" title="Reject">
                                                <x-ui.sccr-icon name="no" :size="18" />
                                            </x-ui.sccr-button>
                                        @endif

                                        {{-- Request Revise --}}
                                        @if (in_array($item['status'], ['pending_rm', 'pending_spv']) && ($canApproveRM || $canApproveSPV))
                                            @php
                                                $reviseLevel = $item['status'] === 'pending_rm' ? 1 : 2;
                                            @endphp
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openActionOverlay('revise', '{{ $item['id'] }}', {{ $reviseLevel }})"
                                                class="text-orange-600 hover:scale-125" title="Request Revise">
                                                <x-ui.sccr-icon name="refresh" :size="18" />
                                            </x-ui.sccr-button>
                                        @endif

                                        {{-- Delete --}}
                                        @if ($item->canBeEdited() && $canDelete)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="deletePR('{{ $item['id'] }}')"
                                                class="text-red-600 hover:scale-125"
                                                wire:confirm="Yakin ingin menghapus PR {{ $item['pr_number'] }}?"
                                                title="Hapus">
                                                <x-ui.sccr-icon name="trash" :size="18" />
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
                <div class="text-sm text-gray-600 flex items-center">
                    <span class="font-bold text-gray-800 mr-1">{{ count($selectedItems) }}</span> item dipilih
                </div>

                <div>
                    {{ $data->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    {{-- ================= ACTION MODALS ================= --}}
    @if ($actionOverlayMode)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="closeActionOverlay">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden" wire:click.stop>
                @php
                    $modalTitle = match($actionOverlayMode) {
                        'approve_rm' => 'Approve PR (Restaurant Manager)',
                        'approve_spv' => 'Approve PR (Supervisor)',
                        'reject' => 'Reject PR',
                        'revise' => 'Request Revise PR',
                        default => 'Action',
                    };
                    $modalColor = match($actionOverlayMode) {
                        'approve_rm', 'approve_spv' => 'bg-green-600',
                        'reject' => 'bg-red-600',
                        'revise' => 'bg-orange-600',
                        default => 'bg-gray-600',
                    };
                @endphp

                <div class="px-6 py-4 border-b {{ $modalColor }} flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">{{ $modalTitle }}</h3>
                    <button wire:click="closeActionOverlay" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">
                            @if ($actionOverlayMode === 'reject')
                                Alasan Reject <span class="text-red-500">*</span>
                            @elseif ($actionOverlayMode === 'revise')
                                Alasan Revise <span class="text-red-500">*</span>
                            @else
                                Catatan (Opsional)
                            @endif
                        </label>
                        <textarea wire:model="actionNotes" rows="3"
                            class="w-full border-gray-300 rounded-md text-sm"
                            placeholder="@if ($actionOverlayMode === 'reject') Alasan reject wajib diisi... @elseif ($actionOverlayMode === 'revise') Alasan revise wajib diisi... @else Tambahkan catatan... @endif"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <x-ui.sccr-button type="button" wire:click="closeActionOverlay"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        Batal
                    </x-ui.sccr-button>

                    @if ($actionOverlayMode === 'approve_rm')
                        <x-ui.sccr-button type="button" wire:click="approveByRM"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Approve
                        </x-ui.sccr-button>
                    @elseif ($actionOverlayMode === 'approve_spv')
                        <x-ui.sccr-button type="button" wire:click="approveBySPV"
                            class="bg-green-600 text-white hover:bg-green-700">
                            Approve
                        </x-ui.sccr-button>
                    @elseif ($actionOverlayMode === 'reject')
                        <x-ui.sccr-button type="button" wire:click="rejectPR"
                            class="bg-red-600 text-white hover:bg-red-700">
                            Reject
                        </x-ui.sccr-button>
                    @elseif ($actionOverlayMode === 'revise')
                        <x-ui.sccr-button type="button" wire:click="requestRevise"
                            class="bg-orange-600 text-white hover:bg-orange-700">
                            Request Revise
                        </x-ui.sccr-button>
                    @endif
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
