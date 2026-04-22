<x-ui.sccr-card transparent wire:key="production-order" class="h-full min-h-0 flex flex-col">

    <div class="relative px-8 py-6 bg-emerald-600/80 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">Production Order</h1>
                <p class="text-emerald-100 text-sm">Manajemen Production Order &amp; Eksekusi Produksi</p>
            </div>
            @if ($canCreate)
                <a href="{{ route('dashboard.resto.recipe.production.create') }}"
                    class="px-4 py-2 bg-white text-emerald-700 rounded-md hover:bg-gray-100 text-sm font-medium font-semibold">
                    + Create Production Order
                </a>
            @endif
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Showing <span class="font-bold text-black">{{ $data->total() }}</span> of <span class="font-bold text-black">{{ $totalAll }}</span> data
            </div>
        </div>
    </div>

    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-1">
            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-1 flex-grow">
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Prod. No</span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Search Prod No..." class="w-64" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Status</span>
                    <x-ui.sccr-select name="filter1" wire:model.live="filter1" :options="$filter1Options" class="w-40" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Approval</span>
                    <x-ui.sccr-select name="filter2" wire:model.live="filter2" :options="$filter2Options" class="w-40" />
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <x-ui.sccr-button type="submit" variant="primary" class="bg-gray-900 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="Search" :size="20" /> Search
                    </x-ui.sccr-button>
                    <x-ui.sccr-button type="button" wire:click="clearFilters" class="bg-gray-800 text-gray-100 hover:bg-gray-400">
                        <x-ui.sccr-icon name="clear" :size="20" /> Clear
                    </x-ui.sccr-button>
                </div>
            </form>

            <div class="flex items-end gap-1 ml-auto">
                <div class="relative top-0">
                    <span class="absolute -top-4 left-1 text-[10px] font-bold text-black uppercase">Show</span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">
            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-900">
                    <thead class="bg-gray-700/80 text-white sticky top-0 z-10">
                        <tr>
                            <th wire:click="sortBy('id')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                ID {!! $sortField === 'id' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th wire:click="sortBy('prod_no')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Prod No {!! $sortField === 'prod_no' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Recipe</th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Version</th>
                            <th class="px-4 py-3 text-right text-xs font-bold">Planned Qty</th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Approval</th>
                            <th wire:click="sortBy('business_date')" class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                BDate {!! $sortField === 'business_date' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($data as $item)
                            <tr class="hover:bg-gray-200 transition">
                                <td class="px-4 py-2 font-mono text-sm font-semibold">{{ $item['id'] }}</td>
                                <td class="px-4 py-2 font-mono text-sm">{{ $item['prod_no'] }}</td>
                                <td class="px-4 py-2 text-sm">{{ $item->recipe?->recipe_name ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm">V{{ $item->recipeVersion?->version_no ?? '-' }}</td>
                                <td class="px-4 py-2 text-sm text-right font-mono">{{ number_format($item['planned_output_qty'], 2) }}</td>
                                <td class="px-4 py-2 text-center">
                                    @php $s = $item['status'] ?? 'draft'; @endphp
                                    @if ($s === 'draft')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                                    @elseif ($s === 'issued')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Issued</span>
                                    @elseif ($s === 'in_progress')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">In Progress</span>
                                    @elseif ($s === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Cancelled</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-center">
                                    @php $a = $item['approval_status'] ?? 'draft'; @endphp
                                    @if ($a === 'draft')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                                    @elseif ($a === 'submitted')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Submitted</span>
                                    @elseif ($a === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm">{{ $item['business_date'] ?? '-' }}</td>
                                <td class="px-4 py-2 text-center">
                                    <button wire:click="goToShow('{{ $item['id'] }}')"
                                        class="text-blue-600 hover:text-blue-800 hover:underline font-medium text-sm">
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-gray-400 italic">No data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600">
                    <span class="font-bold text-gray-800">{{ $data->total() }}</span> production orders
                </div>
                <div>{{ $data->links() }}</div>
            </div>
        </div>
    </div>

    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />
</x-ui.sccr-card>
