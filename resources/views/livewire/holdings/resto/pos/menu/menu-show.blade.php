<div>
    @if ($menu)
        {{-- ================= HEADER ================= --}}
        <div class="relative px-8 py-6 bg-indigo-600/80 rounded-b-3xl shadow-lg overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $menu->name }}</h1>
                    <p class="text-indigo-100 text-sm">
                        {{ $menu->category ?? 'Tidak ada kategori' }} &middot; 
                        Harga: Rp {{ number_format($menu->price, 0, ',', '.') }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('dashboard.resto.menu') }}"
                        class="px-3 py-1 rounded-md text-sm font-medium bg-gray-200 hover:bg-gray-300 text-gray-700">
                        &larr; Kembali
                    </a>
                </div>
            </div>

            <div class="mt-4 flex justify-between items-center text-sm">
                <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            </div>
        </div>

        {{-- ================= INFO CARDS ================= --}}
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Nama Menu</div>
                    <div class="text-lg font-semibold text-gray-800 mt-1">{{ $menu->name }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Kategori</div>
                    <div class="text-lg font-semibold text-gray-800 mt-1">{{ $menu->category ?? '-' }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Harga</div>
                    <div class="text-lg font-semibold text-gray-800 mt-1">Rp {{ number_format($menu->price, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Status Menu</div>
                    <div class="text-lg font-semibold mt-1">
                        @if ($menu->is_active)
                            <span class="text-green-600">Aktif</span>
                        @else
                            <span class="text-red-600">Nonaktif</span>
                        @endif
                    </div>
                </div>
            </div>

            @if ($menu->description)
                <div class="bg-white rounded-xl shadow p-4 mb-6">
                    <div class="text-xs text-gray-500 uppercase font-bold mb-1">Deskripsi</div>
                    <div class="text-sm text-gray-700">{{ $menu->description }}</div>
                </div>
            @endif

            {{-- ================= RESEP SECTION ================= --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Resep Menu</h3>
                    
                    <div class="flex gap-2">
                        @if ($menu->recipe_id)
                            <button wire:click="goToRecipe"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                                Lihat Detail Resep
                            </button>
                        @else
                            @if ($canCreate)
                                <button wire:click="openAddRecipe"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                                    + Tambah Resep
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    @if ($menu->recipe_id && $menu->recipe)
                        {{-- Recipe exists - show summary --}}
                        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-purple-900">{{ $menu->recipe->recipe_name }}</h4>
                                    <p class="text-sm text-purple-600">{{ $menu->recipe->recipe_code }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $menu->recipe->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $menu->recipe->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            @if ($menu->recipe->activeVersion)
                                <div class="border-t border-purple-200 pt-4 mt-4">
                                    <h5 class="text-sm font-medium text-purple-800 mb-2">Versi Aktif: V{{ $menu->recipe->activeVersion->version_no }}</h5>
                                    
                                    @if ($menu->recipe->activeVersion->components->count() > 0)
                                        <div class="overflow-hidden border border-purple-100 rounded-lg">
                                            <table class="min-w-full divide-y divide-purple-100">
                                                <thead class="bg-purple-100/50">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-purple-700 uppercase">#</th>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-purple-700 uppercase">Bahan</th>
                                                        <th class="px-3 py-2 text-right text-xs font-medium text-purple-700 uppercase">Qty</th>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-purple-700 uppercase">Satuan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-purple-100">
                                                    @foreach ($menu->recipe->activeVersion->components as $comp)
                                                        <tr>
                                                            <td class="px-3 py-2 text-sm text-gray-600">{{ $comp->line_no }}</td>
                                                            <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $comp->item?->name ?? '-' }}</td>
                                                            <td class="px-3 py-2 text-sm text-right font-mono">{{ number_format($comp->qty_standard, 4) }}</td>
                                                            <td class="px-3 py-2 text-sm">{{ $comp->uom?->name ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 italic">Versi ini belum memiliki komponen bahan.</p>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-gray-500 italic">Resep ini belum memiliki versi aktif.</p>
                            @endif
                        </div>
                    @else
                        {{-- No recipe - show empty state --}}
                        <div class="text-center py-10">
                            <div class="text-gray-400 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-gray-700 mb-2">Menu ini belum memiliki resep</h4>
                            <p class="text-sm text-gray-500 mb-4 max-w-md mx-auto">
                                Buat resep untuk menu ini agar dapat melakukan produksi dan tracking bahan baku.
                            </p>
                            @if ($canCreate)
                                <button wire:click="openAddRecipe"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium">
                                    + Tambah Resep Sekarang
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="relative px-8 py-6 bg-indigo-600/80 rounded-b-3xl shadow-lg overflow-hidden">
            <h1 class="text-3xl font-bold text-white">Menu Tidak Ditemukan</h1>
            <div class="mt-4">
                <a href="{{ route('dashboard.resto.menu') }}"
                    class="px-4 py-2 bg-white text-indigo-600 rounded-md hover:bg-gray-100 text-sm font-medium">
                    &larr; Kembali ke Daftar Menu
                </a>
            </div>
        </div>
    @endif

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-show-{{ microtime() }}" />

    {{-- ================= OVERLAY: ADD RECIPE ================= --}}
    @if ($overlayMode === 'add-recipe')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto"
                 wire:key="recipe-create-overlay-{{ $menu?->id }}">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>

                @livewire('holdings.resto.resep.recipe.recipe-create', 
                    ['preSelectedMenuId' => $menu?->id],
                    key('recipe-create-'.$menu?->id))
            </div>
        </div>
    @endif

</div>
