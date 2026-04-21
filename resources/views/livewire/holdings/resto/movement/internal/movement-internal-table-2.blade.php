<x-ui.sccr-card transparent wire:key="movement-internal" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stock Movement</h1>
                <p class="text-blue-100 text-sm">
                    Transfer barang antar Location internal (dengan Reference Number)
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Showing <span class="font-bold text-black">{{ $data->total() }}</span> data
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
                        Search
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search"
                        placeholder="Reference Number, Location, Status..." class="w-72" />
                </div>

                {{-- FILTER 1: Status --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Status
                    </span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options" class="w-40" />
                </div>

                {{-- FILTER 2: Type --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Tipe
                    </span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options" class="w-48" />
                </div>

                {{-- FILTER STATUS: Active/Deleted --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Data
                    </span>
                    <select wire:model.live="filterStatus" class="border-gray-300 rounded-md text-sm w-28">
                        <option value="">Semua</option>
                        <option value="active">Active</option>
                        <option value="deleted">Deleted</option>
                    </select>
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-wrap items-center gap-1">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="bg-gray-900 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="Search" :size="20" />
                        Search
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="clearFilters"
                        class="bg-gray-800 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" />
                        Clear
                    </x-ui.sccr-button>

                    {{-- Column Picker Toggle --}}
                    <x-ui.sccr-button type="button" wire:click="toggleColumnPicker"
                        class="bg-gray-700 text-gray-100 hover:bg-gray-400" title="Filter Kolom">
                        <x-ui.sccr-icon name="filter" :size="20" />
                        Kolom
                    </x-ui.sccr-button>

                    {{-- Export Buttons --}}
                    <x-ui.sccr-button type="button" wire:click="exportFiltered"
                        class="bg-green-600 text-gray-100 hover:bg-green-700" title="Export Filtered">
                        <x-ui.sccr-icon name="download" :size="20" />
                        Export
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="exportSelected"
                        class="bg-green-700 text-gray-100 hover:bg-green-800" title="Export Selected">
                        <x-ui.sccr-icon name="download" :size="20" />
                        Export Selected
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

        {{-- Column Picker Dropdown --}}
        @if ($showColumnPicker)
            <div class="mt-2 p-3 bg-white border rounded-lg shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-bold text-gray-700">Pilih Kolom yang Ditampilkan</span>
                    <x-ui.sccr-button type="button" wire:click="resetColumns"
                        class="bg-gray-200 text-gray-700 hover:bg-gray-300 text-xs">
                        Reset
                    </x-ui.sccr-button>
                </div>
                <div class="flex flex-wrap gap-4">
                    @foreach ($availableColumns as $col)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model.live="columnVisibility.{{ $col['key'] }}"
                                class="rounded border-gray-300">
                            <span class="text-gray-700">{{ $col['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
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

                            @if ($columnVisibility['id'] ?? true)
                                <th wire:click="sortBy('id')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['reference_number'] ?? true)
                                <th wire:click="sortBy('reference_number')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    Reference {!! $sortField === 'reference_number' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['from_location_id'] ?? true)
                                <th wire:click="sortBy('from_location_id')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    From {!! $sortField === 'from_location_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['to_location_id'] ?? true)
                                <th wire:click="sortBy('to_location_id')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    To {!! $sortField === 'to_location_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['status'] ?? true)
                                <th wire:click="sortBy('status')"
                                    class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                    Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['pic_name'] ?? true)
                                <th wire:click="sortBy('pic_name')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    PIC {!! $sortField === 'pic_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['remark'] ?? false)
                                <th class="px-3 py-3 text-left text-xs font-bold">
                                    Remark
                                </th>
                            @endif

                            @if ($columnVisibility['created_at'] ?? false)
                                <th wire:click="sortBy('created_at')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    Created {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            @if ($columnVisibility['updated_at'] ?? false)
                                <th wire:click="sortBy('updated_at')"
                                    class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                    Updated {!! $sortField === 'updated_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                                </th>
                            @endif

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Actions</span>

                                    @if ($canCreate && $canWrite)
                                        <x-ui.sccr-button type="button" variant="icon-circle" wire:click="openCreateOverlay"
                                            class="w-8 h-8 hover:scale-105" title="Add Data">
                                            <x-ui.sccr-icon name="plus" :size="18" />
                                        </x-ui.sccr-button>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition {{ $item->deleted_at ? 'bg-red-50' : '' }}">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-2 py-2 text-center">
                                    <input type="checkbox" value="{{ $item['id'] }}" wire:model.live="selectedItems"
                                        class="rounded border-gray-300">
                                </td>

                                @if ($columnVisibility['id'] ?? true)
                                    <td class="px-3 py-2 text-sm font-mono">
                                        {{ $item['id'] }}
                                    </td>
                                @endif

                                @if ($columnVisibility['reference_number'] ?? true)
                                    {{-- Reference Number --}}
                                    <td class="px-3 py-2 font-mono text-sm font-semibold text-blue-700">
                                        {{ $item['reference_number'] ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['from_location_id'] ?? true)
                                    {{-- From Location --}}
                                    <td class="px-3 py-2 text-sm">
                                        {{ $item->fromLocation?->name ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['to_location_id'] ?? true)
                                    {{-- To Location --}}
                                    <td class="px-3 py-2 text-sm">
                                        {{ $item->toLocation?->name ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['status'] ?? true)
                                    {{-- Status --}}
                                    <td class="px-3 py-2 text-center">
                                        @if ($item['status'] === 'draft')
                                            <span class="px-2 py-0.5 rounded bg-gray-200 text-gray-700 text-xs">Draft</span>
                                        @elseif ($item['status'] === 'requested')
                                            @php
                                                $level = $item['approval_level'] ?? 0;
                                                $levelNames = [0 => '', 1 => '> EC', 2 => '> RM', 3 => '> SPV'];
                                            @endphp
                                            <span
                                                class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs">Requested{{ $levelNames[$level] ?? '' }}</span>
                                        @elseif($item['status'] === 'approved')
                                            <span
                                                class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Approved</span>
                                        @elseif($item['status'] === 'in_transit')
                                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">In
                                                Transit</span>
                                        @elseif($item['status'] === 'completed')
                                            <span
                                                class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs">Completed</span>
                                        @elseif($item['status'] === 'rejected')
                                            <span
                                                class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">Rejected</span>
                                        @elseif($item['status'] === 'cancelled')
                                            <span
                                                class="px-2 py-0.5 rounded bg-orange-100 text-orange-800 text-xs">Cancelled</span>
                                        @elseif($item['status'] === 'failed')
                                            <span
                                                class="px-2 py-0.5 rounded bg-red-200 text-red-900 text-xs">Failed</span>
                                        @else
                                            {{ $item['status'] }}
                                        @endif
                                    </td>
                                @endif

                                @if ($columnVisibility['pic_name'] ?? true)
                                    {{-- PIC --}}
                                    <td class="px-3 py-2 text-sm">
                                        {{ $item['pic_name'] ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['remark'] ?? false)
                                    <td class="px-3 py-2 text-sm">
                                        {{ $item['remark'] ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['created_at'] ?? false)
                                    <td class="px-3 py-2 text-sm">
                                        {{ $item['created_at']?->format('Y-m-d H:i') ?? '-' }}
                                    </td>
                                @endif

                                @if ($columnVisibility['updated_at'] ?? false)
                                    <td class="px-3 py-2 text-sm">
                                        {{ $item['updated_at']?->format('Y-m-d H:i') ?? '-' }}
                                    </td>
                                @endif

                                {{-- ROW ACTIONS --}}
                                <td class="px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        @if (! $item->deleted_at)
                                            <a href="{{ route('dashboard.resto.movement-internal.detail', $item['id']) }}"
                                                class="text-gray-700 hover:scale-125" title="Detail">
                                                <x-ui.sccr-icon name="eye" :size="18" />
                                            </a>

                                            {{-- Clone --}}
                                            @if ($canCreate)
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="cloneItem('{{ $item['id'] }}')"
                                                    class="text-blue-600 hover:scale-125" title="Clone">
                                                    <x-ui.sccr-icon name="copy" :size="18" />
                                                </x-ui.sccr-button>
                                            @endif

                                            {{-- Delete --}}
                                            @if ($canDelete)
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="deleteItem('{{ $item['id'] }}')"
                                                    class="text-red-600 hover:scale-125" title="Hapus">
                                                    <x-ui.sccr-icon name="trash" :size="18" />
                                                </x-ui.sccr-button>
                                            @endif
                                        @else
                                            {{-- Restore for deleted items --}}
                                            @if ($canDelete)
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="restoreItem('{{ $item['id'] }}')"
                                                    class="text-green-600 hover:scale-125" title="Restore">
                                                    <x-ui.sccr-icon name="refresh" :size="18" />
                                                </x-ui.sccr-button>
                                            @endif
                                        @endif
                                    </div>

                                    @if (! $item->deleted_at && $item['status'] === 'requested')
                                        @php
                                            $approvalLevel = $item['approval_level'] ?? 0;
                                        @endphp

                                            @if ($ApprovalLevel == 0)
                                                @if ($canApproveExcChef || $canApprove)
                                                    <div class="flex justify-center gap-2 mt-1">
                                                        <x-ui.sccr-button type="button" variant="icon"
                                                            wire:click="excChefCanApprove('{{ $item['id'] }}')"
                                                            class="text-green-600 hover:scale-125"
                                                            title="Approve (Exc Chef)">
                                                            <x-ui.sccr-icon name="approve" :size="18" />
                                                        </x-ui.sccr-button>
                                                    </div>
                                                @endif
                                            @endif

                                            @if ($ApprovalLevel == 1)
                                                @if ($canApproveRM || $canApprove)
                                                    <div class="flex justify-center gap-2 mt-1">
                                                        <x-ui.sccr-button type="button" variant="icon"
                                                            wire:click="rmCanApprove('{{ $item['id'] }}')"
                                                            class="text-green-600 hover:scale-125" title="Approve (RM)">
                                                            <x-ui.sccr-icon name="approve" :size="18" />
                                                        </x-ui.sccr-button>
                                                    </div>
                                                @endif
                                            @endif

                                            @if ($ApprovalLevel == 2)
                                                @if ($canApproveSPV || $canApprove)
                                                    <div class="flex justify-center gap-2 mt-1">
                                                        <x-ui.sccr-button type="button" variant="icon"
                                                            wire:click="spvCanApprove('{{ $item['id'] }}')"
                                                            class="text-green-600 hover:scale-125" title="Approve (SPV)">
                                                            <x-ui.sccr-icon name="approve" :size="18" />
                                                        </x-ui.sccr-button>
                                                    </div>
                                                @endif
                                            @endif
                                        @endif

                                    @if (! $item->deleted_at && in_array($item['status'], ['requested', 'approved']))
                                        <div class="flex justify-center gap-2">
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openRejectOverlay('{{ $item['id'] }}')"
                                                class="text-red-600 hover:scale-125" title="Tolak">
                                                <x-ui.sccr-icon name="no" :size="18" />
                                            </x-ui.sccr-button>
                                        </div>
                                    @endif

                                    @if (! $item->deleted_at && $item['status'] === 'approved')
                                        @if ($canInTransit)
                                            <div class="flex justify-center gap-2">
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="dispatchItems('{{ $item['id'] }}')"
                                                    class="text-orange-600 hover:scale-125"
                                                    title="Kirim (In Transit)">
                                                    <x-ui.sccr-icon name="truck" :size="18" />
                                                </x-ui.sccr-button>
                                            </div>
                                        @endif
                                    @endif

                                    @if (! $item->deleted_at && $item['status'] === 'in_transit')
                                        <div class="flex justify-center gap-2">
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openReceiveOverlay('{{ $item['id'] }}')"
                                                class="text-blue-600 hover:scale-125" title="Terima Barang">
                                                <x-ui.sccr-icon name="paper" :size="18" />
                                            </x-ui.sccr-button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-gray-400 italic">
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
                    <span class="font-bold text-gray-800 mr-1">{{ count($selectedItems) }}</span> items selected
                </div>

                <div>
                    {{ $data->links() }}
                </div>
            </div>

        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    {{-- ================= RECEIVE MODAL ================= --}}
    @if ($receiveOverlayMode === 'receive')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="closeReceiveOverlay">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-blue-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Penerimaan Barang</h3>
                    <button wire:click="closeReceiveOverlay"
                        class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-bold text-gray-700">Item</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-gray-700">Unit</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-gray-700">Qty Request</th>
                                <th class="px-3 py-2 text-center text-xs font-bold text-gray-700">Qty Diterima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($receiveItems as $index => $item)
                                <tr>
                                    <td class="px-3 py-2 text-sm">{{ $item['item_name'] }}</td>
                                    <td class="px-3 py-2 text-center text-sm">{{ $item['uom_name'] }}</td>
                                    <td class="px-3 py-2 text-center text-sm">{{ $item['qty_requested'] }}</td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number" step="0.01" min="0"
                                            max="{{ $item['qty_requested'] }}"
                                            wire:model="receiveItems.{{ $index }}.qty_received"
                                            class="w-20 border-gray-300 rounded text-sm text-center">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea wire:model="receiveNotes" rows="2" class="w-full border-gray-300 rounded-md text-sm"
                            placeholder="Notes penerimaan..."></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <x-ui.sccr-button type="button" wire:click="closeReceiveOverlay"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        Cancel
                    </x-ui.sccr-button>
                    <x-ui.sccr-button type="button" wire:click="processReceive"
                        class="bg-blue-600 text-white hover:bg-blue-700">
                        Terima Barang
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= CREATE MODAL ================= --}}
    @if ($overlayMode === 'create')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="closeOverlay">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-green-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Create Movement: Lokasi → Lokasi</h3>
                    <button wire:click="closeOverlay" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[70vh]">
                    <form wire:submit.prevent="processCreate" class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Request No</label>
                                <input type="text" wire:model="createRequestNumber" readonly class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Request</label>
                                <input type="date" wire:model="createRequestDate" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC</label>
                                <input type="text" wire:model="createPicName" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Nama Penanggung Jawab">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Lokasi <span class="text-red-500">*</span></label>
                                <select wire:model="createFromLocationId" wire:change="onCreateFromLocationChanged" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="0">-- Select Lokasi Asal --</option>
                                    @foreach($this->getCreateFromLocations() as $loc)
                                        <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ke Lokasi <span class="text-red-500">*</span></label>
                                <select wire:model="createToLocationId" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="0">-- Select Lokasi Tujuan --</option>
                                    @foreach($this->getCreateToLocations() as $loc)
                                        <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Daftar Item</label>
                                <button type="button" wire:click="addCreateItemRow" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    + Tambah Item
                                </button>
                            </div>

                            <div class="border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-28">Stok</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-28">Qty</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-12">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($createItems as $index => $item)
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <select wire:model="createItems.{{ $index }}.item_id" class="w-full border-gray-300 rounded-md text-sm">
                                                        <option value="0">-- Select Item --</option>
                                                        @foreach($this->getCreateAvailableItems() as $availItem)
                                                            <option value="{{ $availItem['id'] }}">
                                                                {{ $availItem['name'] }} ({{ $availItem['sku'] }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-3 py-2 text-center text-sm font-mono text-gray-500">
                                                    @php
                                                        $selectedAvail = collect($this->getCreateAvailableItems())->firstWhere('id', $item['item_id']);
                                                    @endphp
                                                    {{ $selectedAvail ? number_format($selectedAvail['available_qty'], 2) : '-' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0.01" wire:model="createItems.{{ $index }}.qty" class="w-full border-gray-300 rounded-md text-sm text-right" placeholder="0">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="text" wire:model="createItems.{{ $index }}.remark" class="w-full border-gray-300 rounded-md text-sm" placeholder="Notes">
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    @if(count($createItems) > 1)
                                                        <button type="button" wire:click="removeCreateItemRow({{ $index }})" class="text-red-600 hover:text-red-800 text-sm" title="Delete">✕</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea wire:model="createRemark" rows="2" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Notes Optional..."></textarea>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Submit
                            </button>
                            <button type="button" wire:click="closeOverlay" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= EDIT MODAL ================= --}}
    @if ($overlayMode === 'edit')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="closeOverlay">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-yellow-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Edit Movement: Lokasi → Lokasi</h3>
                    <button wire:click="closeOverlay" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[70vh]">
                    <form wire:submit.prevent="processEdit" class="space-y-4">
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Request No</label>
                                <input type="text" wire:model="editRequestNumber" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Request</label>
                                <input type="date" wire:model="editRequestDate" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama PIC</label>
                                <input type="text" wire:model="editPicName" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Nama Penanggung Jawab">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Lokasi <span class="text-red-500">*</span></label>
                                <select wire:model="editFromLocationId" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="0">-- Select Lokasi Asal --</option>
                                    @foreach($this->getCreateFromLocations() as $loc)
                                        <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ke Lokasi <span class="text-red-500">*</span></label>
                                <select wire:model="editToLocationId" class="w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="0">-- Select Lokasi Tujuan --</option>
                                    @foreach($this->getCreateToLocations() as $loc)
                                        <option value="{{ $loc['id'] }}">{{ $loc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">Daftar Item</label>
                                <button type="button" wire:click="addEditItemRow" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    + Tambah Item
                                </button>
                            </div>

                            <div class="border rounded-lg overflow-hidden">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-28">Stok</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-28">Qty</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase w-12">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @foreach($editItems as $index => $item)
                                            <tr>
                                                <td class="px-3 py-2">
                                                    <select wire:model="editItems.{{ $index }}.item_id" class="w-full border-gray-300 rounded-md text-sm">
                                                        <option value="0">-- Select Item --</option>
                                                        @foreach($this->getEditAvailableItems() as $availItem)
                                                            <option value="{{ $availItem['id'] }}">
                                                                {{ $availItem['name'] }} ({{ $availItem['sku'] }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-3 py-2 text-center text-sm font-mono text-gray-500">
                                                    @php
                                                        $selectedAvail = collect($this->getEditAvailableItems())->firstWhere('id', $item['item_id']);
                                                    @endphp
                                                    {{ $selectedAvail ? number_format($selectedAvail['available_qty'], 2) : '-' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="number" step="0.01" min="0.01" wire:model="editItems.{{ $index }}.qty" class="w-full border-gray-300 rounded-md text-sm text-right" placeholder="0">
                                                </td>
                                                <td class="px-3 py-2">
                                                    <input type="text" wire:model="editItems.{{ $index }}.remark" class="w-full border-gray-300 rounded-md text-sm" placeholder="Notes">
                                                </td>
                                                <td class="px-3 py-2 text-center">
                                                    @if(count($editItems) > 1)
                                                        <button type="button" wire:click="removeEditItemRow({{ $index }})" class="text-red-600 hover:text-red-800 text-sm" title="Delete">✕</button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea wire:model="editRemark" rows="2" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Notes Optional..."></textarea>
                        </div>

                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                Update
                            </button>
                            <button type="button" wire:click="closeOverlay" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= REJECT MODAL ================= --}}
    @if ($rejectOverlayMode === 'reject')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="closeRejectOverlay">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-red-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Reject Movement</h3>
                    <button wire:click="closeRejectOverlay"
                        class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Reason</label>
                    <textarea wire:model="rejectNotes" rows="4" class="w-full border-gray-300 rounded-md text-sm"
                        placeholder="Reason for rejection..."></textarea>
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <x-ui.sccr-button type="button" wire:click="closeRejectOverlay"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        Cancel
                    </x-ui.sccr-button>
                    <x-ui.sccr-button type="button" wire:click="excChefCanReject('{{ $rejectOverlayId }}')"
                        class="bg-red-600 text-white hover:bg-red-700">
                        Reject
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= DETAIL MODAL ================= --}}
    @if ($overlayMode === 'show' && $overlayId && $this->getDetailData())
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click="closeOverlay">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden" wire:click.stop>
                <div class="px-6 py-4 border-b bg-blue-600 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white">Detail Movement</h3>
                    <button wire:click="closeOverlay" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[70vh]">
                    @php
                        $detail = $this->getDetailData();
                    @endphp

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Reference Number</label>
                            <p class="font-mono font-semibold">{{ $detail->reference_number }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Request Number</label>
                            <p class="font-mono">{{ $detail->request_number ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Request Date</label>
                            <p>{{ $detail->request_date ? $detail->request_date->format('Y-m-d') : '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Status</label>
                            <p class="font-semibold">{{ ucfirst(str_replace('_', ' ', $detail->status)) }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">From</label>
                            <p>{{ $detail->fromLocation?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">To</label>
                            <p>{{ $detail->toLocation?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">PIC</label>
                            <p>{{ $detail->pic_name ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase">Approval Level</label>
                            <p>{{ $detail->approval_level ?? 0 }}</p>
                        </div>
                    </div>

                    <h4 class="font-semibold text-gray-800 mb-2">Items</h4>
                    <table class="min-w-full divide-y divide-gray-200 mb-4">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($detail->items as $item)
                                <tr>
                                    <td class="px-3 py-2 text-sm">{{ $item->item?->name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-center text-sm">{{ $item->uom?->name ?? '-' }}</td>
                                    <td class="px-3 py-2 text-center text-sm font-mono">{{ number_format($item->qty, 2) }}</td>
                                    <td class="px-3 py-2 text-sm">{{ $item->remark ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($this->getStockMutations()->count() > 0)
                        <h4 class="font-semibold text-gray-800 mb-2">Stock Mutations</h4>
                        <div class="max-h-40 overflow-auto border rounded-lg mb-4">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs">Date</th>
                                        <th class="px-3 py-2 text-left text-xs">Type</th>
                                        <th class="px-3 py-2 text-right text-xs">Qty</th>
                                        <th class="px-3 py-2 text-right text-xs">Before</th>
                                        <th class="px-3 py-2 text-right text-xs">After</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($this->getStockMutations() as $mutation)
                                        <tr>
                                            <td class="px-3 py-2 text-xs">{{ $mutation->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                            <td class="px-3 py-2 text-xs font-medium">{{ $mutation->type }}</td>
                                            <td class="px-3 py-2 text-right text-xs font-mono">{{ number_format($mutation->qty, 2) }}</td>
                                            <td class="px-3 py-2 text-right text-xs font-mono">{{ number_format($mutation->qty_before, 2) }}</td>
                                            <td class="px-3 py-2 text-right text-xs font-mono">{{ number_format($mutation->qty_after, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if($this->getRequestActivities()->count() > 0)
                        <h4 class="font-semibold text-gray-800 mb-2">Activity Log</h4>
                        <div class="max-h-40 overflow-auto border rounded-lg">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs">Date</th>
                                        <th class="px-3 py-2 text-left text-xs">Action</th>
                                        <th class="px-3 py-2 text-left text-xs">PIC</th>
                                        <th class="px-3 py-2 text-left text-xs">Comment</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($this->getRequestActivities() as $activity)
                                        <tr>
                                            <td class="px-3 py-2 text-xs">{{ $activity->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                            <td class="px-3 py-2 text-xs font-medium">{{ $activity->action }}</td>
                                            <td class="px-3 py-2 text-xs">{{ $activity->pic }}</td>
                                            <td class="px-3 py-2 text-xs">{{ $activity->comment ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-2">
                    <x-ui.sccr-button type="button" wire:click="closeOverlay"
                        class="bg-gray-500 text-white hover:bg-gray-600">
                        Close
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
