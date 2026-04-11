<x-ui.sccr-card transparent wire:key="movement-internal" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Movement Internal</h1>
                <p class="text-blue-100 text-sm">
                    Transfer barang antar lokasi internal
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
                        placeholder="Doc Number, Item, Lokasi, Type, Status..." class="w-72" />
                </div>

                {{-- FILTER 1: Status --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Status
                    </span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options" class="w-40" />
                </div>

                {{-- FILTER 2: Type --}}
                {{-- <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Tipe
                    </span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options"
                        class="w-40" />
                </div> --}}

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

                    <x-ui.sccr-button type="button" wire:click="exportFiltered" variant="success"
                        class="bg-gray-600 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="exportfiltered" :size="20" />
                        Export Filtered
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportSelected" variant="info"
                        class="bg-gray-500 text-gray-900 hover:bg-gray-400" :disabled="count($selectedItems) === 0">
                        <x-ui.sccr-icon name="exportselected" :size="20" />
                        Export Selected ({{ count($selectedItems) }})
                    </x-ui.sccr-button>
                </div>
            </form>

            {{-- Right: perpage --}}
            <div class="flex items-end gap-1 ml-auto">
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

                            {{-- ID --}}
                            <th wire:click="sortBy('id')" class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Items --}}
                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Items
                            </th>

                            {{-- From Location --}}
                            <th wire:click="sortBy('from_location_id')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                From {!! $sortField === 'from_location_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- To Location --}}
                            <th wire:click="sortBy('to_location_id')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                To {!! $sortField === 'to_location_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Status --}}
                            <th wire:click="sortBy('status')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- PIC --}}
                            <th wire:click="sortBy('pic_id')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                PIC {!! $sortField === 'pic_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Actions --}}
                            <th class="px-3 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Aksi</span>

                                    @if ($canCreate && $canWrite)
                                        <x-ui.sccr-button type="button" variant="icon-circle" wire:click="openCreate"
                                            class="w-8 h-8 hover:scale-105" title="Tambah Data">
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

                                {{-- ID --}}
                                <td class="px-3 py-2 font-mono text-sm font-semibold text-blue-700">
                                    {{ $item['id'] }}
                                </td>

                                {{-- Items --}}
                                <td class="px-3 py-2 text-sm">
                                    @foreach ($item->items as $movementItem)
                                        <div class="mb-1">
                                            <span class="font-medium">{{ $movementItem->item?->name ?? '-' }}</span>
                                            <span
                                                class="text-gray-500 text-xs">({{ number_format($movementItem->qty, 2) }}
                                                {{ $movementItem->uom?->symbols ?? '' }})</span>
                                        </div>
                                    @endforeach
                                </td>

                                {{-- From Location --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item->fromLocation?->name ?? '-' }}
                                </td>

                                {{-- To Location --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item->toLocation?->name ?? '-' }}
                                </td>

                                {{-- Status --}}
                                <td class="px-3 py-2 text-center">
                                    @if ($item['status'] === 'requested')
                                        @php
                                            $level = $item['approval_level'] ?? 0;
                                            $levelNames = [0 => '', 1 => '> EC', 2 => '> RM', 3 => '> SPV'];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">Requested{{ $levelNames[$level] ?? '' }}</span>
                                    @elseif($item['status'] === 'approved')
                                        <span
                                            class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Approved</span>
                                    @elseif($item['status'] === 'in_transit')
                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">In
                                            Transit</span>
                                    @elseif($item['status'] === 'completed')
                                        <span
                                            class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs">Completed</span>
                                    @else
                                        {{ $item['status'] }}
                                    @endif
                                </td>


                                {{-- PIC --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item['pic_name'] ?? '-' }}
                                </td>

                                {{-- ROW ACTIONS --}}
                                <td class="px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        <x-ui.sccr-button type="button" variant="icon"
                                            wire:click="openShow('{{ $item['id'] }}')"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="18" />
                                        </x-ui.sccr-button>
                                    </div>
                                    
                                    @if ($item['status'] === 'requested')
                                        @php
                                            $approvalLevel = $item['approval_level'] ?? 0;
                                        @endphp

                                        {{-- Exc Chef: revise & approve (level 0) --}}
                                        @if ($approvalLevel == 0)
                                            @if ($canApproveExcChef || $canApprove)
                                                <div class="flex justify-center gap-2">
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="excChefCanEdit('{{ $item['id'] }}')"
                                                        class="text-yellow-600 hover:scale-125" title="Edit/Revise Qty">
                                                        <x-ui.sccr-icon name="edit" :size="18" />
                                                    </x-ui.sccr-button>
                                                </div>
                                                <div class="flex justify-center gap-2">
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="excChefCanApprove('{{ $item['id'] }}')"
                                                        class="text-green-600 hover:scale-125" title="Approve (Exc Chef)">
                                                        <x-ui.sccr-icon name="approve" :size="18" />
                                                    </x-ui.sccr-button>
                                                </div>
                                            @endif
                                        @endif

                                        {{-- RM: approve (level 1) --}}
                                        @if ($approvalLevel == 1)
                                            @if ($canApproveRM || $canApprove)
                                                <div class="flex justify-center gap-2">
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="rmCanApprove('{{ $item['id'] }}')"
                                                        class="text-green-600 hover:scale-125" title="Approve (RM)">
                                                        <x-ui.sccr-icon name="approve" :size="18" />
                                                    </x-ui.sccr-button>
                                                </div>
                                            @endif
                                        @endif

                                        {{-- SPV: approve (level 2) --}}
                                        @if ($approvalLevel == 2)
                                            @if ($canApproveSPV || $canApprove)
                                                <div class="flex justify-center gap-2">
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="spvCanApprove('{{ $item['id'] }}')"
                                                        class="text-green-600 hover:scale-125" title="Approve (SPV)">
                                                        <x-ui.sccr-icon name="approve" :size="18" />
                                                    </x-ui.sccr-button>
                                                </div>
                                            @endif
                                        @endif

                                        {{-- Reject button always visible for requested status --}}
                                        <div class="flex justify-center gap-2">
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="excChefCanReject('{{ $item['id'] }}')"
                                                class="text-red-600 hover:scale-125" title="Reject">
                                                <x-ui.sccr-icon name="no" :size="18" />
                                            </x-ui.sccr-button>
                                        </div>
                                    @endif

{{-- Store Keeper: Dispatch (status = approved) --}}
                                    @if ($item['status'] === 'approved')
                                        <!-- DEBUG: canInTransit={{ $canInTransit ? '1' : '0' }}, canApprove={{ $canApprove ? '1' : '0' }} -->
                                        <div class="flex justify-center gap-2">
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="storeKeeperDispatch('{{ $item['id'] }}')"
                                                class="text-blue-600 hover:scale-125" title="Dispatch">
                                                <x-ui.sccr-icon name="truck" :size="18" />
                                            </x-ui.sccr-button>
                                        </div>
                                    @endif

                                    {{-- Store Keeper: Receive actions (status = in_transit) --}}
                                    @if ($item['status'] === 'in_transit')
                                        <div class="flex justify-center gap-2">
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openReceiveOverlay('{{ $item['id'] }}')"
                                                class="text-blue-600 hover:scale-125" title="Terima Barang">
                                                <x-ui.sccr-icon name="paper" :size="18" />
                                            </x-ui.sccr-button>
                                        </div>
                                    @endif

                                    {{-- Completed status (disable) --}}
                                    @if ($item['status'] === 'completed')
                                        <div class="flex justify-center gap-2">
                                            <x-ui.sccr-button type="button" variant="icon"
                                                class="text-gray-400 cursor-not-allowed" title="Completed">
                                                <x-ui.sccr-icon name="check" :size="18" />
                                            </x-ui.sccr-button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-gray-400 italic">
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

    {{-- ================= OVERLAY: CREATE ================= --}}
    @if ($overlayMode === 'create')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.movement.internal.movement-internal-create')
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: SHOW ================= --}}
    @if ($overlayMode === 'show' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                @php
                    $detail = $data->firstWhere('id', $overlayId);
                @endphp
                @if ($detail)
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-4">Detail Movement Internal #{{ $detail['id'] }}</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="font-semibold text-gray-600">From Location:</div>
                            <div>{{ $detail->fromLocation?->name ?? '-' }}</div>

                            <div class="font-semibold text-gray-600">To Location:</div>
                            <div>{{ $detail->toLocation?->name ?? '-' }}</div>

                            <div class="font-semibold text-gray-600">Status:</div>
                            <div>{{ $detail['status'] }}</div>

                            <div class="font-semibold text-gray-600">PIC:</div>
                            <div>{{ $detail['pic_name'] ?? '-' }}</div>

                            <div class="font-semibold text-gray-600">Approved By:</div>
                            <div>{{ $detail['approved_by_name'] ?? '-' }}</div>

                            <div class="font-semibold text-gray-600">Remark:</div>
                            <div>{{ $detail['remark'] ?? '-' }}</div>

                            <div class="font-semibold text-gray-600">Created:</div>
                            <div>{{ $detail['created_at'] }}</div>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Items:</h4>
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Item</th>
                                        <th class="px-3 py-2 text-right">Qty</th>
                                        <th class="px-3 py-2 text-left">Satuan</th>
                                        <th class="px-3 py-2 text-left">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($detail->items as $movementItem)
                                        <tr>
                                            <td class="px-3 py-2">{{ $movementItem->item?->name ?? '-' }}</td>
                                            <td class="px-3 py-2 text-right">
                                                {{ number_format($movementItem->qty, 2) }}</td>
                                            <td class="px-3 py-2">{{ $movementItem->uom?->symbols ?? '' }}</td>
                                            <td class="px-3 py-2">{{ $movementItem->remark ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <p class="text-lg font-semibold">Data tidak ditemukan</p>
            </div>
    @endif
    </div>
    </div>
    @endif

    {{-- ================= OVERLAY: RECEIVE ================= --}}
    @if ($receiveOverlayMode === 'receive' && $receiveOverlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeReceiveOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-hidden flex flex-col">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeReceiveOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                @php
                    $receiveDetail = $data->firstWhere('id', $receiveOverlayId);
                @endphp
                @if ($receiveDetail)
                    <div class="p-6 overflow-y-auto flex-1">
                        <h3 class="text-xl font-bold mb-4">Penerimaan Barang</h3>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="font-semibold text-gray-600">No. Movement:</div>
                                <div class="font-mono font-bold text-blue-700">#{{ $receiveDetail['id'] }}</div>

                                <div class="font-semibold text-gray-600">Dari:</div>
                                <div>{{ $receiveDetail->fromLocation?->name ?? '-' }}</div>

                                <div class="font-semibold text-gray-600">Ke:</div>
                                <div>{{ $receiveDetail->toLocation?->name ?? '-' }}</div>

                                <div class="font-semibold text-gray-600">Status Saat Ini:</div>
                                <div><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">In Transit</span></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="font-semibold text-gray-700 mb-2">Item yang Diterima:</h4>
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Item</th>
                                        <th class="px-3 py-2 text-right">Qty</th>
                                        <th class="px-3 py-2 text-left">Satuan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($receiveDetail->items as $mi)
                                        <tr>
                                            <td class="px-3 py-2">{{ $mi->item?->name ?? '-' }}</td>
                                            <td class="px-3 py-2 text-right font-mono">{{ number_format($mi->qty, 2) }}</td>
                                            <td class="px-3 py-2">{{ $mi->uom?->symbols ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan / Catatan Penerimaan</label>
                            <textarea wire:model="receiveNotes" rows="3"
                                class="w-full border border-gray-300 rounded-md px-3 py-2"
                                placeholder="Contoh: Barang diterima lengkap, kondisi baik..."></textarea>
                        </div>

                        <div class="flex gap-2">
                            <x-ui.sccr-button type="button" wire:click="closeReceiveOverlay"
                                class="flex-1 bg-gray-300 text-gray-700 hover:bg-gray-400">
                                Batal
                            </x-ui.sccr-button>
                            <x-ui.sccr-button type="button" wire:click="confirmReceiveComplete({{ $receiveOverlayId }})"
                                class="flex-1 bg-blue-600 text-white hover:bg-blue-700">
                                Lengkap
                            </x-ui.sccr-button>
                        </div>
                    </div>
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <p class="text-lg font-semibold">Data tidak ditemukan</p>
            </div>
    @endif
    </div>
    </div>
    @endif

    {{-- ================= OVERLAY: EDIT ================= --}}
    @if ($overlayMode === 'edit' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <div class="p-6 text-center text-gray-500">
                    <p class="text-lg font-semibold">Form Edit</p>
                    <p class="text-sm">Coming Soon</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: EDIT-REVISE (Exec Chef) ================= --}}
    @if ($overlayMode === 'edit-revise' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-hidden flex flex-col">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <div class="p-6 overflow-y-auto flex-1">
                    <h3 class="text-lg font-bold mb-4">Revise Qty - Movement #{{ $overlayId }}</h3>
                    
                    <div class="mb-4 flex gap-2 items-end">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tambah Item</label>
                            <select wire:model="reviseItemToAdd" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="0">-- Pilih Item --</option>
                                @foreach($availableItemsForRevise as $availItem)
                                    <option value="{{ $availItem['id'] }}">{{ $availItem['name'] }} (tersedia: {{ $availItem['available'] }} {{ $availItem['uom_symbols'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
                            <input type="number" wire:model="reviseQtyToAdd" step="0.01" min="0"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-center" placeholder="0">
                        </div>
                        <x-ui.sccr-button type="button" wire:click="addItemToRevise"
                            class="bg-green-600 text-white hover:bg-green-700 px-4">
                            + Tambah
                        </x-ui.sccr-button>
                    </div>

                    <div class="max-h-80 overflow-y-auto border rounded-md mb-4">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-100 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2 text-left">Item</th>
                                    <th class="px-3 py-2 text-center">Qty Lama</th>
                                    <th class="px-3 py-2 text-center">Qty Baru</th>
                                    <th class="px-3 py-2 text-center">Satuan</th>
                                    <th class="px-3 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviseItems as $index => $item)
                                    @if(!$item['is_removed'])
                                    <tr class="border-t">
                                        <td class="px-3 py-2 font-medium">{{ $item['item_name'] }}</td>
                                        <td class="px-3 py-2 text-center text-gray-500">{{ number_format($item['qty_original'], 2) }}</td>
                                        <td class="px-3 py-2">
                                            <input type="number" wire:model="reviseItems.{{ $index }}.qty_temp" step="0.01" min="0"
                                                class="w-full border border-gray-300 rounded px-2 py-1 text-center">
                                        </td>
                                        <td class="px-3 py-2 text-center text-gray-500">{{ $item['uom_symbols'] }}</td>
                                        <td class="px-3 py-2 text-center">
                                            <x-ui.sccr-button type="button" variant="icon" wire:click="removeItemFromRevise({{ $index }})"
                                                class="text-red-600 hover:scale-125" title="Hapus">
                                                <x-ui.sccr-icon name="no" :size="16" />
                                            </x-ui.sccr-button>
                                        </td>
                                    </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">Tidak ada items</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex gap-2">
                        <x-ui.sccr-button type="button" wire:click="closeOverlay"
                            class="flex-1 bg-gray-300 text-gray-700 hover:bg-gray-400">
                            Batal
                        </x-ui.sccr-button>
                        <x-ui.sccr-button type="button" wire:click="excChefSaveRevise"
                            class="flex-1 bg-yellow-500 text-white hover:bg-yellow-600">
                            Simpan Revisi
                        </x-ui.sccr-button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
