<div>
    @if ($recipe)
        {{-- ================= HEADER ================= --}}
        <div class="relative px-8 py-6 bg-purple-600/80 rounded-b-3xl shadow-lg overflow-hidden">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $recipe->recipe_name }}</h1>
                    <p class="text-purple-100 text-sm">
                        {{ $recipe->recipe_code }} &middot; Menu: {{ $recipe->menu?->name ?? '-' }}
                        @if ($recipe->menu?->category)
                            <span class="bg-purple-200 text-purple-800 px-2 py-0.5 rounded text-xs font-medium">{{ $recipe->menu->category }}</span>
                        @endif
                    </p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="toggleRecipeActive"
                        class="px-3 py-1 rounded-md text-sm font-medium {{ $recipe->is_active ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-green-500 hover:bg-green-600 text-white' }}">
                        {{ $recipe->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <a href="{{ route('dashboard.resto.recipe.recipe') }}"
                        class="px-3 py-1 rounded-md text-sm font-medium bg-gray-200 hover:bg-gray-300 text-gray-700">
                        &larr; Back
                    </a>
                </div>
            </div>

            <div class="mt-4 flex justify-between items-center text-sm">
                <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            </div>
        </div>

        {{-- ================= INFO CARDS ================= --}}
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Menu</div>
                    <div class="text-lg font-semibold text-gray-800 mt-1">{{ $recipe->menu?->name ?? '-' }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Version Active</div>
                    <div class="text-lg font-semibold text-gray-800 mt-1">
                        @if ($recipe->activeVersion)
                            V{{ $recipe->activeVersion->version_no }}
                            <span class="text-sm text-gray-500">({{ $recipe->activeVersion->components->count() }} Component)</span>
                        @else
                            <span class="text-red-500">No active version</span>
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow p-4">
                    <div class="text-xs text-gray-500 uppercase font-bold">Status</div>
                    <div class="text-lg font-semibold text-gray-800 mt-1">
                        @if ($recipe->is_active)
                            <span class="text-green-600">Active</span>
                        @else
                            <span class="text-red-600">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ================= version SELECTOR ================= --}}
            <div class="bg-white rounded-xl shadow p-4 mb-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-700 uppercase">Select Recipe version</h3>
                    <div class="flex gap-2 flex-wrap">
                        @foreach ($Versionons as $v)
                            <button wire:click="selectversion({{ $v->id }})"
                                class="px-3 py-1.5 rounded-md text-xs font-medium transition
                                {{ $selectedVersionId == $v->id ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                V{{ $v->version_no }}
                                @if ($v->is_active)
                                    <span class="ml-1 text-green-300">&#9679;</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= SELECTED version INFO ================= --}}
            @if ($selectedversion)
                <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 mb-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <div class="text-xs text-purple-600 uppercase font-bold">Version</div>
                            <div class="text-sm font-semibold">V{{ $selectedversion->version_no }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-purple-600 uppercase font-bold">Component</div>
                            <div class="text-sm font-semibold">{{ $selectedversion->components_count }} item</div>
                        </div>
                        <div>
                            <div class="text-xs text-purple-600 uppercase font-bold">Effective From</div>
                            <div class="text-sm font-semibold">{{ $selectedversion->effective_from ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-purple-600 uppercase font-bold">Status</div>
                            <div class="text-sm">
                                @if ($selectedversion->is_active)
                                    <span class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium">Active</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded text-xs font-medium">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @if ($selectedversion->notes)
                        <div class="mt-2 text-xs text-gray-600 italic">{{ $selectedversion->notes }}</div>
                    @endif
                </div>
            @endif

            {{-- ================= TABS ================= --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button wire:click="setActiveTab('Versionons')"
                            class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'Versionons' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Recipe version
                        </button>
                        @if ($selectedVersionId)
                            <button wire:click="setActiveTab('components')"
                                class="px-6 py-3 text-sm font-medium border-b-2 transition {{ $activeTab === 'components' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                                Bahan (BOM)
                                @if ($components->count() > 0)
                                    <span class="ml-1 bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded-full text-xs">{{ $components->count() }}</span>
                                @endif
                            </button>
                        @endif
                    </nav>
                </div>

                {{-- ================= VersionONS TAB ================= --}}
                @if ($activeTab === 'Versionons')
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">version List</h3>
                            @if ($canCreate)
                                <button wire:click="openCreateversion"
                                    class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 text-sm font-medium">
                                    + Add Version Baru
                                </button>
                            @endif
                        </div>

                        @if ($Versionons->isEmpty())
                            <div class="text-center py-10 text-gray-400 italic">
                                Not Yet Recipe version. Klik "Add Version" untuk membuat Version pertama.
                            </div>
                        @else
                            <div class="overflow-hidden border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Version</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Component Count</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($Versionons as $version)
                                            <tr class="hover:bg-gray-50 transition {{ $selectedVersionId == $version->id ? 'bg-purple-50' : '' }}">
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                    <button wire:click="selectversion({{ $version->id }})" class="hover:text-purple-600">
                                                        V{{ $version->version_no }}
                                                    </button>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $version->notes ?? '-' }}</td>
                                                <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $version->components_count }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($version->is_active)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Active</span>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <div class="flex justify-center gap-1">
                                                        @if (! $version->is_active)
                                                            <button wire:click="activateversion({{ $version->id }})" 
                                                                class="px-2 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600" 
                                                                title="Activate">Activate</button>
                                                        @endif
                                                        @if ($canDelete && ! $version->is_active)
                                                            <button wire:click="deleteversion({{ $version->id }})" 
                                                                class="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600" 
                                                                title="Delete" 
                                                                onclick="return confirm('Delete Version ini?')">Delete</button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ================= COMPONENTS (BOM) TAB ================= --}}
                @if ($activeTab === 'components' && $selectedVersionId)
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Ingredients (Bill of Materials)</h3>
                            <p class="text-sm text-gray-500">Version V{{ $selectedversion?->version_no }}</p>
                        </div>

                        @if ($components->isEmpty())
                            <div class="text-center py-10 text-gray-400 italic">
                                No ingredients for this version yet. Buat Version baru untuk menambahkan bahan.
                            </div>
                        @else
                            <div class="overflow-hidden border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingredient Name</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($components as $comp)
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-4 py-3 text-sm text-gray-600">{{ $comp->line_no }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                    {{ $comp->componentItem?->name ?? '-' }}
                                                    <span class="text-xs text-gray-400">{{ $comp->componentItem?->sku ?? '' }}</span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-right font-mono">{{ number_format($comp->qty_standard, 4) }}</td>
                                                <td class="px-4 py-3 text-sm">{{ $comp->uom?->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="relative px-8 py-6 bg-purple-600/80 rounded-b-3xl shadow-lg overflow-hidden">
            <h1 class="text-3xl font-bold text-white">Recipe No Ditemukan</h1>
            <div class="mt-4">
                <a href="{{ route('dashboard.resto.recipe.recipe') }}"
                    class="px-4 py-2 bg-white text-purple-600 rounded-md hover:bg-gray-100 text-sm font-medium">
                    &larr; Back to Recipe List
                </a>
            </div>
        </div>
    @endif

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-show-{{ microtime() }}" />

    {{-- ================= OVERLAY: CREATE version ================= --}}
    @if ($overlayMode === 'create-version')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Close">
                    <span class="text-xl leading-none">&#x2715;</span>
                </x-ui.sccr-button>
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Add Recipe version Baru</h2>
                    <form wire:submit.prevent="storeversion" class="space-y-6">
                        
                        {{-- version Notes --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">version Notes</label>
                            <textarea wire:model="newVersiononNotes"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                rows="2" placeholder="Misal: Version promo Ramadan"></textarea>
                            @error('newVersiononNotes') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        </div>

                        {{-- Components Section --}}
                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-lg font-medium text-gray-800">Recipe Ingredients (BOM) <span class="text-red-500">*</span></h3>
                                <button type="button" wire:click="addVersiononComponent"
                                    class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                    + Add Bahan
                                </button>
                            </div>

                            @if (empty($newVersiononComponents))
                                <div class="bg-gray-50 border border-gray-200 rounded-md p-4 text-center text-gray-500">
                                    Not Yet bahan. Klik "Add Bahan" untuk menambahkan item of kitchen.
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach ($newVersiononComponents as $index => $component)
                                        <div class="bg-gray-50 border border-gray-200 rounded-md p-3 flex gap-3 items-start">
                                            <div class="flex-1">
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Raw Material Item <span class="text-red-500">*</span></label>
                                                <select wire:model="newVersiononComponents.{{ $index }}.item_id"
                                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="">-- Select Item --</option>
                                                    @foreach ($kitchenItems as $item)
                                                        <option value="{{ $item['item_id'] }}">
                                                            {{ $item['item_name'] }} ({{ $item['qty_available'] }} {{ $item['uom_name'] }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("newVersiononComponents.{$index}.item_id")
                                                    <span class="text-red-600 text-xs">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="w-32">
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                                                <input type="number" step="0.01" wire:model="newVersiononComponents.{{ $index }}.qty"
                                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="0.00">
                                                @error("newVersiononComponents.{$index}.qty")
                                                    <span class="text-red-600 text-xs">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="w-32">
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Unit <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model="newVersiononComponents.{{ $index }}.uom_name" readonly
                                                    class="w-full border-gray-300 rounded-md shadow-sm text-sm bg-gray-100">
                                                <input type="hidden" wire:model="newVersiononComponents.{{ $index }}.uom_id">
                                                @error("newVersiononComponents.{$index}.uom_id")
                                                    <span class="text-red-600 text-xs">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="pt-6">
                                                <button type="button" wire:click="removeVersiononComponent({{ $index }})"
                                                    class="text-red-600 hover:text-red-800 p-1"
                                                    title="Delete bahan">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @error('newVersiononComponents')
                                <span class="text-red-600 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-3 pt-4 border-t">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                                Save Version
                            </button>
                            <button type="button" wire:click="closeOverlay" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
