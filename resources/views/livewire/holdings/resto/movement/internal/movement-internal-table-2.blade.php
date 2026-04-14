<x-ui.sccr-card transparent wire:key="movement-internal-2" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-blue-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Movement Internal 2</h1>
                <p class="text-blue-100 text-sm">
                    Transfer barang antar lokasi internal (dengan Reference Number)
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
                        placeholder="Reference Number, Lokasi, Status..." class="w-72" />
                </div>

                {{-- FILTER 1: Status --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Status
                    </span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options" class="w-40" />
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
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            {{-- Reference Number --}}
                            <th wire:click="sortBy('reference_number')" class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                Reference {!! $sortField === 'reference_number' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
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
                            <th wire:click="sortBy('pic_name')"
                                class="px-3 py-3 text-left text-xs font-bold cursor-pointer">
                                PIC {!! $sortField === 'pic_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- Actions --}}
                            <th class="px-3 py-3 text-center text-xs font-bold">
                                Aksi
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

                                {{-- Reference Number --}}
                                <td class="px-3 py-2 font-mono text-sm font-semibold text-blue-700">
                                    {{ $item['reference_number'] ?? '-' }}
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
                                        <span class="px-2 py-0.5 rounded bg-green-100 text-green-800 text-xs">Approved</span>
                                    @elseif($item['status'] === 'in_transit')
                                        <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs">In Transit</span>
                                    @elseif($item['status'] === 'completed')
                                        <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 text-xs">Completed</span>
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
                                        <a href="{{ route('dashboard.resto.movement-internal-2.detail', $item['id']) }}"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="18" />
                                        </a>
                                    </div>

                                    @if ($item['status'] === 'requested')
                                        @php
                                            $approvalLevel = $item['approval_level'] ?? 0;
                                        @endphp

                                        @if ($approvalLevel == 0)
                                            @if ($canApproveExcChef || $canApprove)
                                                <div class="flex justify-center gap-2">
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="excChefCanApprove('{{ $item['id'] }}')"
                                                        class="text-green-600 hover:scale-125" title="Approve (Exc Chef)">
                                                        <x-ui.sccr-icon name="approve" :size="18" />
                                                    </x-ui.sccr-button>
                                                </div>
                                            @endif
                                        @endif

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
                                    @endif

                                    @if (in_array($item['status'], ['requested', 'approved']))
                                        <div class="flex justify-center gap-2">
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openRejectOverlay('{{ $item['id'] }}')"
                                                class="text-red-600 hover:scale-125" title="Tolak">
                                                <x-ui.sccr-icon name="no" :size="18" />
                                            </x-ui.sccr-button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-gray-400 italic">
                                    Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- MODULE FOOTER (pagination) --}}
            <div class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
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