<x-ui.sccr-card transparent wire:key="Menu-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-indigo-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Master Menu</h1>
                <p class="text-indigo-100 text-sm">
                    Manajemen Menu Restoran & Recipe
                </p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Showing <span class="font-bold text-black">{{ $data->total() }}</span> of <span class="font-bold text-black">{{ $totalAll }}</span> data
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
                        Nama / Category
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Type and press enter..."
                        class="w-64" />
                </div>

                {{-- FILTER 1: Category --}}
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Category</span>
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
                        <x-ui.sccr-icon name="Search" :size="20" />
                        Search
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

                            <th wire:click="sortBy('name')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Nama Menu {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('category')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Category {!! $sortField === 'category' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('price')"
                                class="px-4 py-3 text-right text-xs font-bold cursor-pointer">
                                Harga {!! $sortField === 'price' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold">
                                Status Recipe
                            </th>

                            <th wire:click="sortBy('is_active')"
                                class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                Active {!! $sortField === 'is_active' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            {{-- ACTIONS HEADER --}}
                            <th class="px-4 py-3 text-center text-xs font-bold">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition">
                                {{-- ROW CHECKBOX --}}
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $item['id'] }}"
                                        wire:model.live="selectedItems" class="rounded border-gray-300">
                                </td>

                                <td class="px-4 py-2 font-mono text-sm font-semibold">
                                    {{ $item['id'] }}
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    <button wire:click="goToDetail('{{ $item['id'] }}')"
                                        class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                        {{ $item['name'] }}
                                    </button>
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $item['category'] ?? '-' }}
                                </td>

                                <td class="px-4 py-2 text-sm text-right font-mono">
                                    {{ number_format($item['price'], 0, ',', '.') }}
                                </td>

                                <td class="px-4 py-2 text-center text-sm">
                                    @php
                                        $recipe = $item->recipe;
                                        $hasActiveRecipe = $recipe && $recipe->is_active;
                                        $hasactiveVersion = $hasActiveRecipe && $recipe->activeVersion;
                                    @endphp
                                    
                                    @if ($item->recipe_id && $recipe)
                                        @if ($hasactiveVersion)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800" title="Recipe Active - V{{ $recipe->activeVersion->version_no }}">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                V{{ $recipe->activeVersion->version_no }}
                                            </span>
                                        @elseif ($hasActiveRecipe)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Recipe Active tapi Not Yet Version">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600" title="Recipe Inactive">
                                                Inactive
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-600" title="Not Yet punya Recipe">
                                            Not Yet
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-center text-sm">
                                    @if ($item['is_active'])
                                        <span class="text-green-600 font-semibold">Yes</span>
                                    @else
                                        <span class="text-red-600">No</span>
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
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-gray-400 italic">
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

</x-ui.sccr-card>
