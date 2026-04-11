<x-ui.sccr-card transparent wire:key="stock-request" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-purple-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Stock Request Activity</h1>
                <p class="text-purple-100 text-sm">
                    Riwayat aktivitas request movement barang
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
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="PIC/Action/Comment..."
                        class="w-64" />
                </div>

                {{-- FILTER 1: Action --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Action</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options" class="w-40" />
                </div>

                {{-- FILTER 2: Status --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options" class="w-40" />
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
                            <th class="px-2 py-3 text-center w-8">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            <th class="px-2 py-3 text-left text-xs font-bold w-12">
                                ID
                            </th>

                            <th wire:click="sortBy('created_at')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Tanggal {!! $sortField === 'created_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('movement_id')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Movement {!! $sortField === 'movement_id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('pic')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                PIC {!! $sortField === 'pic' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('action')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Action {!! $sortField === 'action' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-3 py-3 text-center text-xs font-bold">
                                Status From
                            </th>

                            <th wire:click="sortBy('status_to')"
                                class="px-3 py-3 text-center text-xs font-bold cursor-pointer">
                                Status To {!! $sortField === 'status_to' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Comment
                            </th>

                            <th class="px-3 py-3 text-left text-xs font-bold">
                                Changes
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            @php
                                $actionColors = [
                                    'requested' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'revised' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'approved' => 'bg-green-100 text-green-800 border-green-200',
                                    'distributed' => 'bg-orange-100 text-orange-800 border-orange-200',
                                    'received' => 'bg-purple-100 text-purple-800 border-purple-200',
                                ];
                                $actionLabels = [
                                    'requested' => 'REQUESTED',
                                    'revised' => 'REVISED',
                                    'approved' => 'APPROVED',
                                    'distributed' => 'DISTRIBUTED',
                                    'received' => 'RECEIVED',
                                ];
                                $statusColors = [
                                    'PENDING' => 'bg-yellow-100 text-yellow-800',
                                    'APPROVED' => 'bg-green-100 text-green-800',
                                    'IN_TRANSIT' => 'bg-orange-100 text-orange-800',
                                    'COMPLETED' => 'bg-purple-100 text-purple-800',
                                ];
                                $badgeClass = $actionColors[$item->action] ?? 'bg-gray-100 text-gray-800';
                                $actionLabel = $actionLabels[$item->action] ?? $item->action;
                                $statusBadgeClass = $statusColors[$item->status_to] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <tr class="hover:bg-gray-200 transition">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-2 py-2 text-center">
                                    <input type="checkbox" value="{{ $item->id }}" wire:model.live="selectedItems"
                                        class="rounded border-gray-300">
                                </td>

                                <td class="px-2 py-2 font-mono text-xs font-semibold">
                                    {{ $item->id }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    <div class="text-gray-800">{{ $item->created_at?->format('Y-m-d') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->created_at?->format('H:i:s') ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-3 py-2 text-center text-sm">
                                    @if ($item->movement)
                                        <span class="text-blue-600 font-medium">#{{ $item->movement_id }}</span>
                                        <div class="text-xs text-gray-500">
                                            {{ $item->movement->fromLocation?->name ?? '-' }} → {{ $item->movement->toLocation?->name ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    <span class="font-medium">{{ $item->pic ?? '-' }}</span>
                                </td>

                                <td class="px-3 py-2 text-center">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border {{ $badgeClass }}">
                                        {{ $actionLabel }}
                                    </span>
                                </td>

                                <td class="px-3 py-2 text-center text-sm">
                                    <span class="text-gray-500">{{ $item->status_from ?? '-' }}</span>
                                </td>

                                <td class="px-3 py-2 text-center">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold {{ $statusBadgeClass }}">
                                        {{ $item->status_to ?? '-' }}
                                    </span>
                                </td>

                                <td class="px-3 py-2 text-sm text-gray-500 max-w-48 truncate">
                                    {{ $item->comment ?? '-' }}
                                </td>

                                <td class="px-3 py-2 text-sm">
                                    @if ($item->changes)
                                        @php 
                                            $changes = is_string($item->changes) ? json_decode($item->changes, true) : $item->changes;
                                            $itemName = $changes['item_name'] ?? null;
                                            $qtyFrom = null;
                                            $qtyTo = null;
                                            
                                            if (isset($changes['qty']['from']) && isset($changes['qty']['to'])) {
                                                $qtyFrom = $changes['qty']['from'];
                                                $qtyTo = $changes['qty']['to'];
                                            } elseif (isset($changes['qty']) && is_numeric($changes['qty'])) {
                                                $qtyFrom = $changes['qty'];
                                            }
                                        @endphp
                                        @if ($itemName)
                                            <span class="text-xs font-medium text-blue-600">{{ $itemName }}</span>
                                        @endif
                                        @if ($qtyFrom !== null && $qtyTo !== null)
                                            <span class="text-xs font-mono bg-gray-200 px-1 rounded ml-1">
                                                ({{ $qtyFrom }} → {{ $qtyTo }})
                                            </span>
                                        @elseif ($qtyFrom !== null)
                                            <span class="text-xs font-mono bg-gray-200 px-1 rounded ml-1">
                                                qty: {{ $qtyFrom }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-10 text-center text-gray-400 italic">
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

</x-ui.sccr-card>