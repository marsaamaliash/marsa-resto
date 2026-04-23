<x-ui.sccr-card transparent wire:key="purchase-request-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Purchase Request</h1>
                <p class="text-blue-100 text-sm">
                    Purchase request (PR) with multi-level approval
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Showing <span class="font-bold text-black">{{ $data->total() }}</span> of <span class="font-bold text-black">{{ $data->total() }}</span> data
            </div>
        </div>
    </div>

    {{-- ================= FILTERS & ACTIONS ================= --}}
    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-2">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-1 flex-grow">

                {{-- SEARCH INPUT --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Search
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search"
                        placeholder="PR Number, Location, Item..." class="w-72" />
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
                        Location
                    </span>
                    <x-ui.sccr-select name="filterLocation" wire:model.live="filterLocation" :options="$this->filterLocationOptions" class="w-48" />
                </div>

                {{-- ACTION BUTTONS --}}
                <div class="flex flex-wrap items-center gap-1 mb-1">
                    <x-ui.sccr-button type="submit" variant="primary"
                        class="bg-gray-900 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="cari" :size="20" />
                        Search
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="clearFilters"
                        class="bg-gray-800 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" />
                        Clear
                    </x-ui.sccr-button>
                </div>
            </form>

            {{-- Right: perpage & export --}}
            <div class="flex items-end gap-2 ml-auto">
                @if ($canExport)
                    <x-ui.sccr-button type="button" wire:click="exportExcel"
                        class="bg-green-600 text-white hover:bg-green-700">
                        <x-ui.sccr-icon name="file-excel" :size="18" />
                        Export Excel
                    </x-ui.sccr-button>
                @endif

                <div class="flex flex-col">
                    <label class="text-[10px] font-bold text-black uppercase mb-1">Show</label>
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

                            {{-- Location --}}
                            <th wire:click="sortBy('requester_location_id')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Location {!! $sortField === 'requester_location_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Requester --}}
                            <th wire:click="sortBy('requested_by')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Requester {!! $sortField === 'requested_by' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Total Items --}}
                            <th class="px-3 py-3 text-center text-xs font-bold">
                                Items
                            </th>

                            {{-- Created At --}}
                            <th wire:click="sortBy('created_at')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Created At {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Created By --}}
                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Created By
                            </th>

                            {{-- Updated At --}}
                            <th wire:click="sortBy('updated_at')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Updated At {!! $sortField === 'updated_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Updated By --}}
                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Updated By
                            </th>

                            {{-- Status --}}
                            <th wire:click="sortBy('status')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Status {!! $sortField === 'status' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Actions --}}
                            <th class="px-4 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Actions</span>

                                    @if ($canCreate)
                                        <x-ui.sccr-button type="button" variant="icon-circle" wire:click="openCreateFromCritical"
                                            class="w-8 h-8 hover:scale-105" title="Create PR from Critical Stock">
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

                                {{-- Location --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item->requesterLocation?->name ?? '-' }}
                                </td>

                                {{-- Requester --}}
                                <td class="px-3 py-2 text-sm">
                                    {{ $item['requested_by'] ?? '-' }}
                                </td>

                                {{-- Total Items --}}
                                <td class="px-3 py-2 text-center text-sm">
                                    <span class="font-semibold">{{ $item->items->count() }}</span>
                                    @if ($item->items->where('is_critical', true)->count() > 0)
                                        <span class="text-red-600 text-xs" title="Critical Stock">
                                            ({{ $item->items->where('is_critical', true)->count() }} critical)
                                        </span>
                                    @endif
                                </td>

                                {{-- Created At --}}
                                <td class="px-3 py-2 text-sm text-gray-600">
                                    {{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                {{-- Created By --}}
                                <td class="px-3 py-2 text-sm text-gray-600">
                                    {{ $item['created_by'] ?? '-' }}
                                </td>

                                {{-- Updated At --}}
                                <td class="px-3 py-2 text-sm text-gray-600">
                                    {{ $item->updated_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                {{-- Updated By --}}
                                <td class="px-3 py-2 text-sm text-gray-600">
                                    {{ $item['updated_by'] ?? '-' }}
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

                                {{-- ROW ACTIONS --}}
                                <td class="px-3 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        {{-- View Detail --}}
                                        <a href="{{ route('dashboard.resto.purchase-request.detail', $item['id']) }}"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="18" />
                                        </a>

                                        {{-- Edit --}}
                                        @if ($item->canBeEdited() && ($canCreate || $canUpdate))
                                            <a href="{{ route('dashboard.resto.purchase-request.detail', $item['id']) }}?mode=edit"
                                                class="text-blue-600 hover:scale-125" title="Edit">
                                                <x-ui.sccr-icon name="edit" :size="18" />
                                            </a>
                                        @endif

                                        {{-- Submit to RM --}}
                                        @if ($item->canBeEdited() && $canCreate)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="submitDraftPRToRM({{ $item['id'] }})"
                                                class="text-orange-600 hover:scale-125" title="Submit to RM">
                                                <x-ui.sccr-icon name="send" :size="18" />
                                            </x-ui.sccr-button>
                                        @endif

                                        {{-- Delete --}}
                                        @if ($item->canBeEdited() && $canDelete)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="deletePR('{{ $item['id'] }}')"
                                                class="text-red-600 hover:scale-125"
                                                wire:confirm="Are you sure you want to delete PR {{ $item['pr_number'] }}?"
                                                title="Delete">
                                                <x-ui.sccr-icon name="trash" :size="18" />
                                            </x-ui.sccr-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                @empty
                    <tr>
                        <td colspan="11" class="py-10 text-center text-gray-400 italic">
                                    No data found
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
                    <span class="font-bold text-gray-800 mr-1">{{ count($selectedItems) }}</span> item(s) selected
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
