<x-ui.sccr-card transparent wire:key="sso-role-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start gap-3">
            <div>
                <h1 class="text-3xl font-bold text-white">Roles & Access</h1>
                <p class="text-slate-200 text-sm">
                    Kelola Role + editor <b>Module Access</b> & <b>Permissions</b> (overlay, autoscroll)
                </p>
            </div>

            @if (!empty($canCreate) && $canCreate)
                <x-ui.sccr-button type="button" wire:click="openCreate"
                    class="bg-white/10 hover:bg-white/20 text-white border border-white/20">
                    <span class="inline-flex items-center gap-2">
                        <span class="text-lg leading-none">➕</span>
                        <span class="text-sm font-semibold">Role Baru</span>
                    </span>
                </x-ui.sccr-button>
            @endif
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Total <span class="font-bold text-yellow-300">{{ $rows->total() }}</span> role
            </div>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2 flex-grow">
                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Cari code /
                        name</span>
                    <x-ui.sccr-input wire:model.live="search" placeholder="DEV / STAFF / MGR..." class="w-72" />
                </div>
            </div>

            <div class="flex items-end gap-2 ml-auto">
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

    {{-- ================= TABLE ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-slate-700/90 text-white sticky top-0 z-10">
                        <tr>
                            <th wire:click="sortBy('code')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Code {!! $sortField === 'code' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th wire:click="sortBy('name')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Name {!! $sortField === 'name' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Users</th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Modules</th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Permissions</th>
                            <th class="px-4 py-3 text-center text-xs font-bold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-50">
                        @forelse ($rows as $r)
                            <tr class="hover:bg-gray-100 transition">
                                <td class="px-4 py-2 text-sm font-mono font-bold">{{ $r->code }}</td>
                                <td class="px-4 py-2 text-sm">{{ $r->name }}</td>
                                <td class="px-4 py-2 text-center text-sm font-semibold">
                                    {{ (int) ($r->users_count ?? 0) }}</td>
                                <td class="px-4 py-2 text-center text-sm font-semibold">
                                    {{ (int) ($r->modules_count ?? 0) }}</td>
                                <td class="px-4 py-2 text-center text-sm font-semibold">
                                    {{ (int) ($r->permissions_count ?? 0) }}</td>

                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-2">
                                        @if (!empty($canUpdate) && $canUpdate)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openEdit({{ (int) $r->id }})"
                                                class="text-blue-700 hover:scale-125" title="Edit Role">
                                                <span class="text-[18px] leading-none">✏️</span>
                                            </x-ui.sccr-button>
                                        @endif

                                        @if (!empty($canRoleModuleUpdate) && $canRoleModuleUpdate)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openModules({{ (int) $r->id }})"
                                                class="text-slate-900 hover:scale-125" title="Edit Module Access">
                                                <span class="text-[18px] leading-none">📦</span>
                                            </x-ui.sccr-button>
                                        @endif

                                        @if (!empty($canRolePermissionUpdate) && $canRolePermissionUpdate)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openPermissions({{ (int) $r->id }})"
                                                class="text-emerald-700 hover:scale-125" title="Edit Permissions">
                                                <span class="text-[18px] leading-none">🔑</span>
                                            </x-ui.sccr-button>
                                        @endif

                                        @if (empty($canUpdate) && empty($canRoleModuleUpdate) && empty($canRolePermissionUpdate))
                                            <span class="text-xs text-gray-400 italic">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-gray-400 italic">
                                    Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div
                class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600">
                    {{ $rows->firstItem() ?? 0 }}-{{ $rows->lastItem() ?? 0 }} dari {{ $rows->total() }}
                </div>
                <div>{{ $rows->links() }}</div>
            </div>

        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    {{-- ================= OVERLAY: ROLE FORM ================= --}}
    @if (in_array($overlayMode, ['create', 'edit'], true))
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6">
            <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl relative overflow-hidden">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <div class="p-6">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Role</div>
                    <h2 class="text-xl font-extrabold text-gray-900">
                        {{ $overlayMode === 'create' ? 'Buat Role Baru' : 'Edit Role' }}
                    </h2>

                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="text-sm font-bold text-gray-700">Role Code</label>
                            <input type="text" wire:model.defer="role_code"
                                class="w-full border-gray-300 rounded-lg text-sm mt-1"
                                placeholder="contoh: STAFF_HR / HEAD_RT / DEV" />
                            <div class="text-[11px] text-gray-500 mt-1">Maks 30 karakter.</div>
                        </div>

                        <div>
                            <label class="text-sm font-bold text-gray-700">Role Name</label>
                            <input type="text" wire:model.defer="role_name"
                                class="w-full border-gray-300 rounded-lg text-sm mt-1"
                                placeholder="contoh: Staff HR / Head RT / Developer" />
                            <div class="text-[11px] text-gray-500 mt-1">Maks 255 karakter.</div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <x-ui.sccr-button type="button" variant="secondary" wire:click="closeOverlay">
                                Batal
                            </x-ui.sccr-button>
                            <x-ui.sccr-button type="button" variant="success" wire:click="saveRole">
                                Simpan
                            </x-ui.sccr-button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: MODULES ================= --}}
    @if ($overlayMode === 'modules' && $overlayRoleId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl relative overflow-hidden">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <div class="p-5 border-b bg-gray-50">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Modules Access</div>
                    <h2 class="text-xl font-extrabold text-gray-900">
                        {{-- Edit Module Access — Role ID: <span class="font-mono">{{ $overlayRoleId }}</span> --}}
                        Edit Module Access — Role Name: <span class="font-mono">{{ $role_name }}</span>
                    </h2>
                </div>

                <div class="p-4 max-h-[70vh] overflow-auto">
                    <div class="flex justify-end mb-3">
                        <x-ui.sccr-button type="button" variant="success" wire:click="saveModules">
                            Simpan Modules
                        </x-ui.sccr-button>
                    </div>

                    <div class="overflow-auto rounded-xl border">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-slate-700 text-white sticky top-0 z-10">
                                <tr>
                                    <th class="px-3 py-2 text-center text-xs font-bold w-14">On</th>
                                    <th class="px-3 py-2 text-left text-xs font-bold">Module</th>
                                    <th class="px-3 py-2 text-left text-xs font-bold">Route</th>
                                    <th class="px-3 py-2 text-center text-xs font-bold w-28">Access</th>
                                    <th class="px-3 py-2 text-center text-xs font-bold w-40">Scope Type</th>
                                    <th class="px-3 py-2 text-left text-xs font-bold">Scope</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($modulesList as $m)
                                    @php
                                        $code = (string) ($m['code'] ?? '');
                                        $route = (string) ($m['route'] ?? '');
                                        $name = (string) ($m['name'] ?? '');
                                        $form = $modulesForm[$code] ?? [];
                                        $scopeType = $form['scope_type'] ?? '';
                                    @endphp

                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-center">
                                            <input type="checkbox" class="rounded border-gray-300"
                                                wire:model.live="modulesForm.{{ $code }}.enabled">
                                        </td>

                                        <td class="px-3 py-2 text-sm">
                                            <div class="font-mono font-bold">{{ $code }}</div>
                                            <div class="text-xs text-gray-500">{{ $name }}</div>
                                        </td>

                                        <td class="px-3 py-2 text-xs font-mono text-gray-700">
                                            {{ $route !== '' ? $route : '-' }}
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <select wire:model.live="modulesForm.{{ $code }}.access_level"
                                                class="border-gray-300 rounded-lg text-sm">
                                                <option value="view">view</option>
                                                <option value="full">full</option>
                                            </select>
                                        </td>

                                        <td class="px-3 py-2 text-center">
                                            <select wire:model.live="modulesForm.{{ $code }}.scope_type"
                                                class="border-gray-300 rounded-lg text-sm">
                                                <option value="">global</option>
                                                <option value="holding">holding</option>
                                                <option value="department">department</option>
                                                <option value="division">division</option>
                                            </select>
                                        </td>

                                        <td class="px-3 py-2 text-xs">
                                            @if ($scopeType === 'holding')
                                                <select wire:model.live="modulesForm.{{ $code }}.holding_id"
                                                    class="w-full border-gray-300 rounded-lg text-sm">
                                                    <option value="">-- pilih holding --</option>
                                                    @foreach ($holdingOptions as $id => $label)
                                                        <option value="{{ $id }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif ($scopeType === 'department')
                                                <select
                                                    wire:model.live="modulesForm.{{ $code }}.department_id"
                                                    class="w-full border-gray-300 rounded-lg text-sm">
                                                    <option value="">-- pilih department --</option>
                                                    @foreach ($departmentOptions as $id => $label)
                                                        <option value="{{ $id }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @elseif ($scopeType === 'division')
                                                <select wire:model.live="modulesForm.{{ $code }}.division_id"
                                                    class="w-full border-gray-300 rounded-lg text-sm">
                                                    <option value="">-- pilih division --</option>
                                                    @foreach ($divisionOptions as $id => $label)
                                                        <option value="{{ $id }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <span class="text-gray-400 italic">global (no scope)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <x-ui.sccr-button type="button" variant="success" wire:click="saveModules">
                            Simpan Modules
                        </x-ui.sccr-button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= OVERLAY: PERMISSIONS ================= --}}
    @if ($overlayMode === 'permissions' && $overlayRoleId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl relative overflow-hidden">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <div class="p-5 border-b bg-gray-50">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Permissions</div>
                    <h2 class="text-xl font-extrabold text-gray-900">
                        {{-- Edit Permissions — Role ID: <span class="font-mono">{{ $overlayRoleId }}</span> --}}
                        Edit Permissions — Role Name: <span class="font-mono">{{ $role_name }}</span>
                    </h2>

                    <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                        <div class="flex-1 min-w-[240px]">
                            <label class="text-xs font-bold text-gray-700">Cari permission</label>
                            <input type="text" wire:model.live="permissionSearch"
                                class="w-full border-gray-300 rounded-lg text-sm mt-1"
                                placeholder="ketik: INV_DELETE / EMP_VIEW / SSO_..." />
                        </div>

                        <div class="flex items-center gap-2">
                            <x-ui.sccr-button type="button" variant="success" wire:click="savePermissions">
                                Simpan Permissions
                            </x-ui.sccr-button>
                        </div>
                    </div>
                </div>

                <div class="p-4 max-h-[70vh] overflow-auto">
                    @php
                        $groups = $this->filteredPermissionGroups();
                    @endphp

                    @forelse ($groups as $mc => $g)
                        <div class="rounded-2xl border mb-3 overflow-hidden">
                            <div class="px-4 py-2 bg-slate-800 text-white flex items-center justify-between">
                                <div class="text-sm font-bold">
                                    <span class="font-mono">{{ $g['module_code'] ?? $mc }}</span>
                                    <span class="opacity-80">· {{ $g['module_name'] ?? '' }}</span>
                                </div>
                                <div class="text-xs opacity-80">{{ count($g['items'] ?? []) }} perms</div>
                            </div>

                            <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-2 bg-white">
                                @foreach ($g['items'] ?? [] as $p)
                                    @php
                                        $pid = (int) ($p['id'] ?? 0);
                                    @endphp

                                    <label class="flex items-start gap-3 p-2 rounded-xl border hover:bg-gray-50">
                                        <input type="checkbox" class="rounded border-gray-300 mt-1"
                                            wire:model.live="selectedPermissionIds.{{ $pid }}">
                                        <div class="min-w-0">
                                            <div class="text-xs font-mono font-bold text-gray-900">
                                                {{ $p['code'] ?? '-' }}

                                                @if (!empty($p['requires_approval']))
                                                    <span
                                                        class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-900">
                                                        approval
                                                    </span>
                                                @endif
                                            </div>

                                            @if (!empty($p['desc']))
                                                <div class="text-[11px] text-gray-500">{{ $p['desc'] }}</div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center text-gray-400 italic">Tidak ada permission sesuai pencarian.
                        </div>
                    @endforelse

                    <div class="mt-4 flex justify-end">
                        <x-ui.sccr-button type="button" variant="success" wire:click="savePermissions">
                            Simpan Permissions
                        </x-ui.sccr-button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</x-ui.sccr-card>
