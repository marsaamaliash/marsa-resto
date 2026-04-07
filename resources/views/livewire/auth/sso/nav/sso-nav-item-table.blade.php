<x-ui.sccr-card transparent wire:key="sso-nav-item-table" class="h-full min-h-0 flex flex-col">

    {{-- HEADER --}}
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Menu Editor</h1>
                <p class="text-slate-200 text-sm">
                    CRUD <span class="font-mono">auth_nav_items</span> (root + submenu) — mode:
                    <b>{{ $mode === 'tree' ? 'Tree' : 'List' }}</b>
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- toggle tree/list --}}
                <x-ui.sccr-button type="button" wire:click="$set('treeMode', {{ $mode === 'tree' ? 'false' : 'true' }})"
                    class="bg-slate-700 hover:bg-slate-600 text-white h-[34px] px-4 text-sm">
                    {{ $mode === 'tree' ? '📄 List Mode' : '🌳 Tree Mode' }}
                </x-ui.sccr-button>

                @if ($mode === 'tree')
                    <x-ui.sccr-button type="button" wire:click="expandAll"
                        class="bg-slate-700 hover:bg-slate-600 text-white h-[34px] px-3 text-sm">
                        ➕ Expand All
                    </x-ui.sccr-button>

                    <x-ui.sccr-button type="button" wire:click="collapseAll"
                        class="bg-slate-700 hover:bg-slate-600 text-white h-[34px] px-3 text-sm">
                        ➖ Collapse All
                    </x-ui.sccr-button>
                @endif

                @if ($canCreate)
                    <x-ui.sccr-button type="button" wire:click="openCreate"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white h-[34px] px-4 text-sm">
                        ➕ Tambah Menu
                    </x-ui.sccr-button>
                @endif
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Total <span class="font-bold text-yellow-300">{{ (int) ($total ?? 0) }}</span> item
            </div>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="px-4 pt-3 pb-2">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div class="flex flex-wrap items-end gap-2">
                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Cari</span>
                    <x-ui.sccr-input wire:model.live="search" placeholder="nav_code / label / route / permission"
                        class="w-80" />
                </div>

                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Module</span>
                    <x-ui.sccr-select wire:model.live="filterModule" :options="['' => 'All'] + $moduleOptions" class="w-64" />
                </div>

                <div class="relative">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Active</span>
                    <x-ui.sccr-select wire:model.live="filterActive" :options="['' => 'All', '1' => 'Active', '0' => 'Inactive']" class="w-32" />
                </div>
            </div>

            {{-- perPage hanya untuk list --}}
            <div class="flex items-end gap-2 ml-auto">
                <div class="relative">
                    <span class="absolute -top-4 left-1 text-[10px] font-bold text-black uppercase">Show</span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm"
                        {{ $mode === 'tree' ? 'disabled' : '' }}>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    @if ($mode === 'tree')
                        <div class="text-[10px] text-gray-500 mt-1">Tree mode tanpa pagination</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-slate-700/90 text-white sticky top-0 z-10">
                        <tr>
                            <th wire:click="sortBy('nav_code')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                nav_code {!! $sortField === 'nav_code' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th wire:click="sortBy('label')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Label {!! $sortField === 'label' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold">Parent</th>
                            <th wire:click="sortBy('module_code')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Module {!! $sortField === 'module_code' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-bold">route_name</th>
                            <th class="px-4 py-3 text-left text-xs font-bold">permission</th>
                            <th wire:click="sortBy('sort_order')"
                                class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                Order {!! $sortField === 'sort_order' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th wire:click="sortBy('is_active')"
                                class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                Active {!! $sortField === 'is_active' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-50">
                        @if ($mode === 'tree')
                            @forelse($flatRows as $r)
                                @php
                                    $depth = (int) ($r['_depth'] ?? 0);
                                    $pad = $depth * 18; // px
                                    $hasChildren = (bool) ($r['_has_children'] ?? false);
                                    $isExpanded = (bool) ($expanded[(int) $r['id']] ?? false);
                                @endphp

                                <tr class="hover:bg-gray-100 transition">
                                    <td class="px-4 py-2 text-xs font-mono font-semibold">
                                        <div class="flex items-center gap-2"
                                            style="padding-left: {{ $pad }}px;">
                                            @if ($hasChildren)
                                                <button type="button" wire:click="toggleExpand({{ (int) $r['id'] }})"
                                                    class="text-gray-700 hover:text-black">
                                                    {{ $isExpanded ? '▼' : '▶' }}
                                                </button>
                                            @else
                                                <span class="text-gray-300">•</span>
                                            @endif

                                            <span>{{ $r['nav_code'] }}</span>

                                            @if ((int) ($r['children_count'] ?? 0) > 0)
                                                <span class="text-[10px] text-slate-500">
                                                    ({{ (int) $r['children_count'] }})
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-2 text-sm">
                                        <span class="text-lg">{{ $r['icon'] ?: '' }}</span>
                                        <span class="font-semibold">{{ $r['label'] }}</span>
                                    </td>

                                    <td class="px-4 py-2 text-xs text-gray-700">
                                        @if (!empty($r['parent_code']))
                                            <div class="font-mono">{{ $r['parent_code'] }}</div>
                                            <div class="text-[11px] text-gray-500">{{ $r['parent_label'] }}</div>
                                        @else
                                            <span class="text-gray-400 italic">ROOT</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-2 text-xs">
                                        <div class="font-mono font-semibold">{{ $r['module_code'] }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $r['module_name'] ?: '-' }}</div>
                                    </td>

                                    <td class="px-4 py-2 text-xs font-mono text-gray-700">
                                        {{ $r['route_name'] ?: '-' }}
                                    </td>

                                    <td class="px-4 py-2 text-xs font-mono text-gray-700">
                                        {{ $r['permission_code'] ?: '-' }}
                                    </td>

                                    <td class="px-4 py-2 text-center text-xs font-semibold">
                                        {{ (int) $r['sort_order'] }}
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <x-ui.sccr-badge :type="(int) $r['is_active'] === 1 ? 'success' : 'danger'">
                                            {{ (int) $r['is_active'] === 1 ? 'Active' : 'Inactive' }}
                                        </x-ui.sccr-badge>
                                    </td>

                                    <td class="px-4 py-2 text-center">
                                        <div class="flex justify-center gap-2">
                                            @if ($canUpdate)
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="openEdit({{ (int) $r['id'] }})"
                                                    class="text-blue-600 hover:scale-125" title="Edit">
                                                    ✏️
                                                </x-ui.sccr-button>

                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="toggleActive({{ (int) $r['id'] }})"
                                                    class="text-slate-800 hover:scale-125" title="Toggle Active">
                                                    {{ (int) $r['is_active'] === 1 ? '😎' : '👁️' }}
                                                </x-ui.sccr-button>
                                            @endif

                                            @if ($canDelete)
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="openDelete({{ (int) $r['id'] }})"
                                                    class="text-rose-700 hover:scale-125"
                                                    title="Delete (cascade children!)">
                                                    🗑️
                                                </x-ui.sccr-button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-10 text-center text-gray-400 italic">Tidak ada data
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            @forelse($rows as $r)
                                <tr class="hover:bg-gray-100 transition">
                                    <td class="px-4 py-2 text-xs font-mono font-semibold">
                                        {{ $r->nav_code }}
                                        @if ((int) $r->children_count > 0)
                                            <div class="text-[10px] text-slate-500">children:
                                                {{ (int) $r->children_count }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="text-lg">{{ $r->icon ?: '' }}</span>
                                        <span class="font-semibold">{{ $r->label }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-xs text-gray-700">
                                        @if ($r->parent_code)
                                            <div class="font-mono">{{ $r->parent_code }}</div>
                                            <div class="text-[11px] text-gray-500">{{ $r->parent_label }}</div>
                                        @else
                                            <span class="text-gray-400 italic">ROOT</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-xs">
                                        <div class="font-mono font-semibold">{{ $r->module_code }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $r->module_name ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-2 text-xs font-mono text-gray-700">
                                        {{ $r->route_name ?: '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-xs font-mono text-gray-700">
                                        {{ $r->permission_code ?: '-' }}
                                    </td>
                                    <td class="px-4 py-2 text-center text-xs font-semibold">
                                        {{ (int) $r->sort_order }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <x-ui.sccr-badge :type="(int) $r->is_active === 1 ? 'success' : 'danger'">
                                            {{ (int) $r->is_active === 1 ? 'Active' : 'Inactive' }}
                                        </x-ui.sccr-badge>
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <div class="flex justify-center gap-2">
                                            @if ($canUpdate)
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="openEdit({{ (int) $r->id }})"
                                                    class="text-blue-600 hover:scale-125" title="Edit">
                                                    ✏️
                                                </x-ui.sccr-button>

                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="toggleActive({{ (int) $r->id }})"
                                                    class="text-slate-800 hover:scale-125" title="Toggle Active">
                                                    {{ (int) $r->is_active === 1 ? '😎' : '👁️' }}
                                                </x-ui.sccr-button>
                                            @endif

                                            @if ($canDelete)
                                                <x-ui.sccr-button type="button" variant="icon"
                                                    wire:click="openDelete({{ (int) $r->id }})"
                                                    class="text-rose-700 hover:scale-125"
                                                    title="Delete (cascade children!)">
                                                    🗑️
                                                </x-ui.sccr-button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-10 text-center text-gray-400 italic">Tidak ada data
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- FOOTER --}}
            <div class="flex-none px-6 py-3 border-t bg-white flex justify-between items-center">
                @if ($mode === 'list' && $rows)
                    <div class="text-sm text-gray-600">
                        {{ $rows->firstItem() ?? 0 }}-{{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }}
                    </div>
                    <div>{{ $rows->links() }}</div>
                @else
                    <div class="text-sm text-gray-600">
                        Tree mode — gunakan Expand/Collapse. (tanpa pagination)
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ trim($search) !== '' ? 'Search aktif: menampilkan match + ancestor.' : '' }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL CREATE/EDIT --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ $modalMode === 'create' ? 'Tambah Menu' : 'Edit Menu' }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Kelola item pada <span class="font-mono">auth_nav_items</span>.
                        </p>
                    </div>

                    <x-ui.sccr-button type="button" variant="icon" wire:click="closeModal"
                        class="text-gray-500 hover:text-gray-800" title="Tutup">
                        <span class="text-xl leading-none">×</span>
                    </x-ui.sccr-button>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold text-gray-700">nav_code</label>
                        <input wire:model.defer="nav_code" class="w-full border-gray-300 rounded-lg text-sm mt-1"
                            placeholder="contoh: 01005.LOKASI" />
                        <div class="text-[11px] text-gray-500 mt-1">Unique, maks 50 char.</div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-700">Parent</label>
                        <select wire:model.defer="parent_id" class="w-full border-gray-300 rounded-lg text-sm mt-1">
                            @foreach ($parentOptions as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                        <div class="text-[11px] text-gray-500 mt-1">Root jika tanpa parent.</div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-700">Module</label>
                        <select wire:model.defer="module_code" class="w-full border-gray-300 rounded-lg text-sm mt-1">
                            <option value="">— pilih module —</option>
                            @foreach ($moduleOptions as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                        <div class="text-[11px] text-gray-500 mt-1">Module menentukan gate akses & sidebar.</div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-700">Label</label>
                        <input wire:model.defer="label" class="w-full border-gray-300 rounded-lg text-sm mt-1"
                            placeholder="contoh: Master Lokasi" />
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-700">route_name (opsional)</label>
                        <input wire:model.defer="route_name" class="w-full border-gray-300 rounded-lg text-sm mt-1"
                            placeholder="contoh: holdings.hq.sdm.rt.inventaris.master.lokasi.table" />
                        <div class="text-[11px] text-gray-500 mt-1">Jika kosong = group menu (header folder).</div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-700">permission_code (opsional)</label>
                        <select wire:model.defer="permission_code"
                            class="w-full border-gray-300 rounded-lg text-sm mt-1">
                            <option value="">— none —</option>
                            @foreach ($permissionOptions as $k => $v)
                                <option value="{{ $k }}">{{ $v }}</option>
                            @endforeach
                        </select>
                        <div class="text-[11px] text-gray-500 mt-1">Harus 1 module dengan item.</div>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-700">Icon (emoji)</label>
                        <input wire:model.defer="icon" class="w-full border-gray-300 rounded-lg text-sm mt-1"
                            placeholder="contoh: 📦" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-bold text-gray-700">sort_order</label>
                            <input type="number" wire:model.defer="sort_order"
                                class="w-full border-gray-300 rounded-lg text-sm mt-1" />
                        </div>
                        <div>
                            <label class="text-xs font-bold text-gray-700">is_active</label>
                            <select wire:model.defer="is_active"
                                class="w-full border-gray-300 rounded-lg text-sm mt-1">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-ui.sccr-button type="button" variant="secondary" wire:click="closeModal">
                        Batal
                    </x-ui.sccr-button>
                    <x-ui.sccr-button type="button" variant="success" wire:click="save">
                        Simpan
                    </x-ui.sccr-button>
                </div>
            </div>
        </div>
    @endif

    {{-- DELETE CONFIRM MODAL --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Hapus Menu (Hard Delete)</h3>
                        <p class="text-xs text-rose-700 mt-1">
                            HATI-HATI: karena FK <b>ON DELETE CASCADE</b>, anak submenu ikut terhapus.
                        </p>
                    </div>

                    <x-ui.sccr-button type="button" variant="icon" wire:click="cancelDelete"
                        class="text-gray-500 hover:text-gray-800" title="Tutup">
                        <span class="text-xl leading-none">×</span>
                    </x-ui.sccr-button>
                </div>

                <div class="mt-4">
                    <label class="text-sm font-bold text-gray-700">Ketik nav_code untuk konfirmasi</label>
                    <input wire:model.defer="deleteConfirm" class="w-full border-gray-300 rounded-lg text-sm mt-1"
                        placeholder="contoh: 01005.LOKASI" />
                    <div class="text-[11px] text-gray-500 mt-1">Harus sama persis.</div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-ui.sccr-button type="button" variant="secondary"
                        wire:click="cancelDelete">Batal</x-ui.sccr-button>
                    <x-ui.sccr-button type="button" variant="danger"
                        wire:click="confirmDelete">Hapus</x-ui.sccr-button>
                </div>
            </div>
        </div>
    @endif

    {{-- TOAST --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

</x-ui.sccr-card>
