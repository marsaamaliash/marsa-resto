<x-ui.sccr-card transparent wire:key="recipe" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-purple-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Resep Menu</h1>
                <p class="text-purple-100 text-sm">
                    Manajemen Resep & Bahan (BOM)
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Menampilkan <span class="font-bold text-black">{{ $data->total() }}</span> dari <span class="font-bold text-black">{{ $totalAll }}</span> data
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
                        Kode / Nama / Menu
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik lalu enter..."
                        class="w-64" />
                </div>

                {{-- FILTER 1: Menu Category --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Kategori</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options"
                        class="w-40" />
                </div>

                {{-- FILTER 2: Status --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options"
                        class="w-40" />
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

    {{-- ================= TABLE ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            {{-- TABLE SCROLLER --}}
            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-900">
                    <thead class="bg-gray-700/80 text-white sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            <th wire:click="sortBy('id')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('recipe_code')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Kode {!! $sortField === 'recipe_code' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('recipe_name')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Nama Resep {!! $sortField === 'recipe_name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold">
                                Menu / Tipe
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                Versi Aktif
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                Jml Komponen
                            </th>

                            <th wire:click="sortBy('is_active')"
                                class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                Aktif {!! $sortField === 'is_active' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- ACTIONS HEADER --}}
                            <th class="px-4 py-3 text-center text-xs font-bold">
                                <div class="flex items-center justify-center gap-2">
                                    <span>Aksi</span>

                                    @if ($canCreate && $canWrite)
                                        <div class="flex gap-1">
                                            <x-ui.sccr-button type="button" variant="icon-circle"
                                                wire:click="openCreate" class="w-8 h-8 hover:scale-105"
                                                title="Tambah Resep Menu">
                                                <x-ui.sccr-icon name="plus" :size="18" />
                                            </x-ui.sccr-button>
                                            <x-ui.sccr-button type="button" variant="icon-circle"
                                                wire:click="openCreateSemiFinished" class="w-8 h-8 hover:scale-105 bg-orange-600"
                                                title="Tambah Resep Semi-Finished">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                </svg>
                                            </x-ui.sccr-button>
                                        </div>
                                    @endif
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            @php
                                $activeVersion = $item->activeVersion;
                            @endphp
                            <tr class="hover:bg-gray-200 transition">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $item['id'] }}"
                                        wire:model.live="selectedItems" class="rounded border-gray-300">
                                </td>

                                <td class="px-4 py-2 font-mono text-sm font-semibold">
                                    {{ $item['id'] }}
                                </td>

                                <td class="px-4 py-2 font-mono text-sm">
                                    {{ $item['recipe_code'] }}
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    <button wire:click="goToDetail('{{ $item['id'] }}')"
                                        class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                        {{ $item['recipe_name'] }}
                                    </button>
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    @if ($item->menu_id)
                                        {{ $item->menu?->name ?? '-' }}
                                        @if ($item->menu?->category)
                                            <span class="text-xs text-gray-400">({{ $item->menu->category }})</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                            Semi-Finished
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-center text-sm">
                                    @if ($activeVersion)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                            V{{ $activeVersion->version_no }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-center text-sm">
                                    @if ($activeVersion)
                                        {{ $activeVersion->components->count() }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-center text-sm">
                                    @if ($item['is_active'])
                                        <span class="text-green-600 font-semibold">Ya</span>
                                    @else
                                        <span class="text-red-600">Tidak</span>
                                    @endif
                                </td>

                                {{-- ROW ACTIONS --}}
                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-3">
                                        <x-ui.sccr-button type="button" variant="icon"
                                            wire:click="goToDetail('{{ $item['id'] }}')"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="20" />
                                        </x-ui.sccr-button>

                                        @if ($canDelete)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="deleteRecipe('{{ $item['id'] }}')"
                                                class="text-red-600 hover:scale-125" title="Hapus"
                                                onclick="return confirm('Yakin ingin menghapus resep ini?')">
                                                <x-ui.sccr-icon name="delete" :size="20" />
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

    {{-- ================= OVERLAY: CREATE ================= --}}
    @if ($overlayMode === 'create')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.resep.recipe.recipe-create')
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: CREATE SEMI-FINISHED ================= --}}
    @if ($overlayMode === 'create-semi-finished')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.resep.recipe.recipe-create', ['isSemiFinished' => true])
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: EDIT ================= --}}
    @if ($overlayMode === 'edit' && $overlayId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.resep.recipe.recipe-edit', ['id' => $overlayId])
            </div>
        </div>
    @endif

</x-ui.sccr-card>
