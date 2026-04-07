<div class="p-4 sm:p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Access</div>
            <h2 class="text-xl font-extrabold text-gray-900">
                {{ $target['username'] ?? 'User' }}
                @if (!empty($target['email']))
                    <span class="text-sm font-semibold text-gray-500">({{ $target['email'] }})</span>
                @endif
            </h2>

            <div class="text-xs text-gray-600 mt-1">
                Identity: <span class="font-mono font-semibold">{{ $target['identity_type'] ?? '-' }}</span> ·
                <span class="font-mono">{{ $target['identity_key'] ?? '-' }}</span>
            </div>

            <div class="text-xs text-gray-600">
                Scope: <span class="font-semibold">{{ $target['holding_alias'] ?? '-' }}</span> /
                {{ $target['department_name'] ?? '-' }} /
                {{ $target['division_name'] ?? '-' }}
            </div>

            @if ((int) ($target['is_super_admin'] ?? 0) === 1)
                <div
                    class="mt-2 inline-flex items-center gap-2 px-2 py-1 rounded-full bg-rose-50 text-rose-700 text-[11px] font-bold">
                    🛡️ SUPER ADMIN
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <x-ui.sccr-button type="button" variant="secondary" wire:click="close">
                Tutup
            </x-ui.sccr-button>
        </div>
    </div>

    {{-- toast kecil di overlay --}}
    <div class="mt-3">
        <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']"
            wire:key="toast-ov-{{ microtime() }}" />
    </div>

    <div class="mt-4 border rounded-2xl overflow-hidden bg-white shadow-sm">
        <div class="px-3 py-2 bg-gray-50 border-b" x-data="{ tab: 'roles' }">
            <div class="flex flex-wrap gap-2">
                <button type="button" class="px-3 py-1.5 rounded-full text-xs font-bold"
                    :class="tab === 'roles' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border'"
                    @click="tab='roles'">
                    Roles
                </button>
                <button type="button" class="px-3 py-1.5 rounded-full text-xs font-bold"
                    :class="tab === 'modules' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border'"
                    @click="tab='modules'">
                    Effective Modules
                </button>
                <button type="button" class="px-3 py-1.5 rounded-full text-xs font-bold"
                    :class="tab === 'perms' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border'"
                    @click="tab='perms'">
                    Effective Permissions
                </button>
                <button type="button" class="px-3 py-1.5 rounded-full text-xs font-bold"
                    :class="tab === 'overrides' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border'"
                    @click="tab='overrides'">
                    Overrides
                </button>
            </div>

            {{-- BODY --}}
            <div class="p-3 sm:p-4 max-h-[70vh] overflow-auto">

                {{-- ROLES --}}
                <div x-show="tab==='roles'" x-cloak>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-gray-900">Role Assignment</div>
                            <div class="text-xs text-gray-500">
                                Ubah akses user dengan menambah / menghapus role (ERP best practice).
                            </div>
                        </div>

                        @if ($canRoleAssign)
                            <x-ui.sccr-button type="button" variant="success" wire:click="saveRoles">
                                Simpan Roles
                            </x-ui.sccr-button>
                        @else
                            <div
                                class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl">
                                Read-only (tidak punya izin <span class="font-mono">SSO_USER_ROLE_ASSIGN</span>)
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach ($allRoles as $r)
                            <label class="flex items-center gap-3 p-3 rounded-xl border hover:bg-gray-50">
                                <input type="checkbox" value="{{ (int) $r['id'] }}" wire:model.live="selectedRoleIds"
                                    class="rounded border-gray-300" @disabled(!$canRoleAssign)>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900">{{ $r['code'] }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $r['name'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- MODULES --}}
                <div x-show="tab==='modules'" x-cloak>
                    <div class="text-sm font-bold text-gray-900">Effective Modules</div>
                    <div class="text-xs text-gray-500">
                        Hasil akhir akses module (role_modules + module overrides).
                    </div>

                    <div class="mt-3 space-y-2">
                        @forelse ($effectiveModules as $m)
                            <div class="p-3 rounded-xl border bg-white">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">
                                            <span class="font-mono">{{ $m['module_code'] }}</span> ·
                                            {{ $m['module_name'] }}
                                        </div>
                                        @if (!empty($m['route']))
                                            <div class="text-xs text-gray-500 font-mono">{{ $m['route'] }}</div>
                                        @endif
                                    </div>

                                    <x-ui.sccr-badge :type="$m['access_level'] === 'full' ? 'success' : 'info'">
                                        {{ strtoupper($m['access_level']) }}
                                    </x-ui.sccr-badge>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 italic">Tidak ada module</div>
                        @endforelse
                    </div>
                </div>

                {{-- PERMISSIONS --}}
                <div x-show="tab==='perms'" x-cloak>
                    <div class="text-sm font-bold text-gray-900">Effective Permissions</div>
                    <div class="text-xs text-gray-500">
                        Hasil akhir permission (role_permissions + permission overrides) dan <b>HANYA dari module yang
                            effective</b>.
                    </div>

                    <div class="mt-3 space-y-3">
                        @forelse ($effectivePermissionsGrouped as $grp)
                            <div class="rounded-xl border bg-white overflow-hidden">
                                <div class="px-3 py-2 bg-gray-50 border-b">
                                    <div class="text-sm font-bold text-gray-900">
                                        <span class="font-mono">{{ $grp['module_code'] }}</span> ·
                                        {{ $grp['module_name'] }}
                                    </div>
                                </div>

                                <div class="p-3 space-y-2">
                                    @foreach ($grp['items'] as $p)
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-xs font-mono font-semibold text-gray-900">
                                                    {{ $p['code'] }}
                                                </div>
                                                @if (!empty($p['description']))
                                                    <div class="text-[11px] text-gray-500">{{ $p['description'] }}
                                                    </div>
                                                @endif
                                            </div>

                                            @if ((int) $p['requires_approval'] === 1)
                                                <span
                                                    class="px-2 py-1 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-900">
                                                    approval
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 italic">Tidak ada permission</div>
                        @endforelse
                    </div>
                </div>

                {{-- OVERRIDES --}}
                <div x-show="tab==='overrides'" x-cloak>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-bold text-gray-900">User Overrides</div>
                            <div class="text-xs text-gray-500">
                                Override per-user untuk kasus khusus (contoh: Putra STAFF bisa DELETE tapi STAFF lain
                                tidak).
                                <br>
                                <b>Catatan:</b> Module adalah gate paling depan. Permission allow tidak akan efektif
                                jika module-nya tidak allowed.
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($canOverrideWrite)
                                <x-ui.sccr-button type="button" variant="secondary" wire:click="resetOverrideForm">
                                    Reset Form
                                </x-ui.sccr-button>
                            @else
                                <div
                                    class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl">
                                    Read-only (tidak punya izin edit override)
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- CLONE ACCESS --}}
                    <div class="mt-4 rounded-2xl border bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-bold text-gray-900">Clone Access (Template)</div>
                                <div class="text-xs text-gray-600">
                                    Copy hak akses dari user lain (roles + overrides) ke user ini.
                                    <br><b>Catatan:</b> identity scope target tetap, jadi pastikan scope-nya sesuai.
                                </div>
                            </div>

                            @if (!$canCloneAccess)
                                <div
                                    class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl">
                                    Read-only (tidak punya izin clone)
                                </div>
                            @endif
                        </div>

                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="md:col-span-2">
                                <label class="text-xs font-bold text-gray-700">Source User (template)</label>
                                <select wire:model.live="cloneFromUserId"
                                    class="mt-1 w-full border-gray-300 rounded-lg text-sm"
                                    @disabled(!$canCloneAccess)>
                                    <option value="">-- pilih user template --</option>
                                    @foreach ($cloneUserOptions as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-700">Mode</label>
                                <select wire:model.live="cloneMode"
                                    class="mt-1 w-full border-gray-300 rounded-lg text-sm"
                                    @disabled(!$canCloneAccess)>
                                    <option value="replace">REPLACE (hapus akses target lalu copy)</option>
                                    <option value="merge">MERGE (gabung + update yang sama)</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-700">Overrides copied</label>
                                <label class="mt-2 flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="cloneOnlyActiveOverrides"
                                        class="rounded border-gray-300" @disabled(!$canCloneAccess)>
                                    <span>Copy hanya override yang Active</span>
                                </label>
                            </div>

                            <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="cloneRoles"
                                        class="rounded border-gray-300" @disabled(!$canCloneAccess)>
                                    <span>Roles</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="cloneModuleOverrides"
                                        class="rounded border-gray-300" @disabled(!$canCloneAccess)>
                                    <span>Module Overrides</span>
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" wire:model.live="clonePermissionOverrides"
                                        class="rounded border-gray-300" @disabled(!$canCloneAccess)>
                                    <span>Permission Overrides</span>
                                </label>
                            </div>

                            <div class="md:col-span-2 flex justify-end">
                                @if ($canCloneAccess)
                                    <x-ui.sccr-button type="button" variant="primary"
                                        wire:click="cloneAccessFromUser"
                                        class="bg-slate-900 text-white hover:bg-slate-700">
                                        Clone Access ke User Ini
                                    </x-ui.sccr-button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- FORM --}}
                    <div class="mt-4 rounded-2xl border bg-white p-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-bold text-gray-700">Jenis Override</label>
                                <select wire:model.live="ovType"
                                    class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                    <option value="module">Module Override</option>
                                    <option value="permission">Permission Override</option>
                                </select>
                                @if ($ovId)
                                    <div class="mt-1 text-[11px] text-indigo-700 font-semibold">Editing ID:
                                        {{ $ovId }}</div>
                                @endif
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-700">Effect</label>
                                <select wire:model.live="ovEffect"
                                    class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                    <option value="allow">ALLOW</option>
                                    <option value="deny">DENY</option>
                                </select>
                            </div>

                            @if ($ovType === 'module')
                                <div class="sm:col-span-2">
                                    <label class="text-xs font-bold text-gray-700">Module</label>
                                    <select wire:model.live="ovModuleCode"
                                        class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                        <option value="">-- pilih module --</option>
                                        @foreach ($moduleOptions as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-gray-700">Access Level (ALLOW)</label>
                                    <select wire:model.live="ovAccessLevel"
                                        class="mt-1 w-full border-gray-300 rounded-lg text-sm"
                                        @disabled($ovEffect !== 'allow')>
                                        <option value="view">view</option>
                                        <option value="full">full</option>
                                    </select>
                                    <div class="text-[11px] text-gray-500 mt-1">Jika DENY, access_level diabaikan.
                                    </div>
                                </div>
                            @else
                                <div class="sm:col-span-2">
                                    <label class="text-xs font-bold text-gray-700">Permission</label>
                                    <select wire:model.live="ovPermissionCode"
                                        class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                        <option value="">-- pilih permission --</option>
                                        @foreach ($permissionOptions as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <label class="text-xs font-bold text-gray-700">Scope Type</label>
                                <select wire:model.live="ovScopeType"
                                    class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                    <option value="global">global</option>
                                    <option value="holding">holding</option>
                                    <option value="department">department</option>
                                    <option value="division">division</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-700">Scope ID</label>

                                @if ($ovScopeType === 'global')
                                    <div class="mt-1 text-sm text-gray-500 italic">Global (tanpa ID)</div>
                                @elseif ($ovScopeType === 'holding')
                                    <select wire:model.live="ovScopeHoldingId"
                                        class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                        <option value="">-- pilih holding --</option>
                                        @foreach ($holdingOptions as $id => $label)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($ovScopeType === 'department')
                                    <select wire:model.live="ovScopeDepartmentId"
                                        class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                        <option value="">-- pilih department --</option>
                                        @foreach ($departmentOptions as $id => $label)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select wire:model.live="ovScopeDivisionId"
                                        class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                        <option value="">-- pilih division --</option>
                                        @foreach ($divisionOptions as $id => $label)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @endif

                                <div class="text-[11px] text-gray-500 mt-1">Default otomatis diambil dari identity
                                    scope user (boleh diubah).</div>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-xs font-bold text-gray-700">Reason (opsional, max 255)</label>
                                <input wire:model.live="ovReason"
                                    class="mt-1 w-full border-gray-300 rounded-lg text-sm"
                                    placeholder="Contoh: exception khusus (Putra boleh delete)" />
                            </div>

                            <div>
                                <label class="text-xs font-bold text-gray-700">Status</label>
                                <select wire:model.live="ovIsActive"
                                    class="mt-1 w-full border-gray-300 rounded-lg text-sm">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <div class="flex items-end justify-end">
                                @if ($canOverrideWrite)
                                    <x-ui.sccr-button type="button" variant="success" wire:click="saveOverride">
                                        Simpan Override
                                    </x-ui.sccr-button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- LISTS --}}
                    <div class="mt-4 grid grid-cols-1 gap-4">
                        {{-- MODULE OVERRIDES --}}
                        <div class="rounded-2xl border bg-white overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b">
                                <div class="text-sm font-bold text-gray-900">Module Overrides</div>
                                <div class="text-xs text-gray-500">ALLOW/DENY module per user.</div>
                            </div>

                            <div class="p-3 overflow-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="text-xs text-gray-500">
                                        <tr>
                                            <th class="text-left py-2">Status</th>
                                            <th class="text-left py-2">Module</th>
                                            <th class="text-left py-2">Effect</th>
                                            <th class="text-left py-2">Access</th>
                                            <th class="text-left py-2">Scope</th>
                                            <th class="text-left py-2">Reason</th>
                                            <th class="text-right py-2">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @forelse ($moduleOverrides as $o)
                                            <tr>
                                                <td class="py-2">
                                                    <x-ui.sccr-badge :type="(int) $o['is_active'] === 1 ? 'success' : 'danger'">
                                                        {{ (int) $o['is_active'] === 1 ? 'Active' : 'Inactive' }}
                                                    </x-ui.sccr-badge>
                                                </td>
                                                <td class="py-2 font-mono font-semibold">{{ $o['module_code'] }}</td>
                                                <td
                                                    class="py-2 font-bold {{ $o['effect'] === 'deny' ? 'text-rose-700' : 'text-emerald-700' }}">
                                                    {{ strtoupper($o['effect']) }}
                                                </td>
                                                <td class="py-2">{{ $o['access_level'] }}</td>
                                                <td class="py-2 text-xs text-gray-600">
                                                    {{ $o['scope_type'] }}
                                                    @if ($o['scope_holding_id'])
                                                        #{{ $o['scope_holding_id'] }}
                                                    @endif
                                                    @if ($o['scope_department_id'])
                                                        #{{ $o['scope_department_id'] }}
                                                    @endif
                                                    @if ($o['scope_division_id'])
                                                        #{{ $o['scope_division_id'] }}
                                                    @endif
                                                </td>
                                                <td class="py-2 text-xs text-gray-600">{{ $o['reason'] ?: '-' }}</td>
                                                <td class="py-2 text-right">
                                                    <div class="inline-flex gap-2">
                                                        <button class="text-indigo-700 text-xs font-bold"
                                                            type="button"
                                                            wire:click="editModuleOverride({{ (int) $o['id'] }})">Edit</button>
                                                        @if ($canOverrideWrite)
                                                            <button class="text-amber-700 text-xs font-bold"
                                                                type="button"
                                                                wire:click="toggleOverrideActive('module', {{ (int) $o['id'] }})">
                                                                {{ (int) $o['is_active'] === 1 ? 'Disable' : 'Enable' }}
                                                            </button>
                                                            <button class="text-rose-700 text-xs font-bold"
                                                                type="button"
                                                                wire:click="deleteOverride('module', {{ (int) $o['id'] }})">
                                                                Hapus
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-6 text-center text-gray-400 italic">Belum
                                                    ada module override</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- PERMISSION OVERRIDES --}}
                        <div class="rounded-2xl border bg-white overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b">
                                <div class="text-sm font-bold text-gray-900">Permission Overrides</div>
                                <div class="text-xs text-gray-500">ALLOW/DENY permission per user (tetap module-gated).
                                </div>
                            </div>

                            <div class="p-3 overflow-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="text-xs text-gray-500">
                                        <tr>
                                            <th class="text-left py-2">Status</th>
                                            <th class="text-left py-2">Permission</th>
                                            <th class="text-left py-2">Effect</th>
                                            <th class="text-left py-2">Scope</th>
                                            <th class="text-left py-2">Reason</th>
                                            <th class="text-right py-2">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        @forelse ($permissionOverrides as $o)
                                            <tr>
                                                <td class="py-2">
                                                    <x-ui.sccr-badge :type="(int) $o['is_active'] === 1 ? 'success' : 'danger'">
                                                        {{ (int) $o['is_active'] === 1 ? 'Active' : 'Inactive' }}
                                                    </x-ui.sccr-badge>
                                                </td>
                                                <td class="py-2 font-mono font-semibold">{{ $o['permission_code'] }}
                                                </td>
                                                <td
                                                    class="py-2 font-bold {{ $o['effect'] === 'deny' ? 'text-rose-700' : 'text-emerald-700' }}">
                                                    {{ strtoupper($o['effect']) }}
                                                </td>
                                                <td class="py-2 text-xs text-gray-600">
                                                    {{ $o['scope_type'] }}
                                                    @if ($o['scope_holding_id'])
                                                        #{{ $o['scope_holding_id'] }}
                                                    @endif
                                                    @if ($o['scope_department_id'])
                                                        #{{ $o['scope_department_id'] }}
                                                    @endif
                                                    @if ($o['scope_division_id'])
                                                        #{{ $o['scope_division_id'] }}
                                                    @endif
                                                </td>
                                                <td class="py-2 text-xs text-gray-600">{{ $o['reason'] ?: '-' }}</td>
                                                <td class="py-2 text-right">
                                                    <div class="inline-flex gap-2">
                                                        <button class="text-indigo-700 text-xs font-bold"
                                                            type="button"
                                                            wire:click="editPermissionOverride({{ (int) $o['id'] }})">Edit</button>
                                                        @if ($canOverrideWrite)
                                                            <button class="text-amber-700 text-xs font-bold"
                                                                type="button"
                                                                wire:click="toggleOverrideActive('permission', {{ (int) $o['id'] }})">
                                                                {{ (int) $o['is_active'] === 1 ? 'Disable' : 'Enable' }}
                                                            </button>
                                                            <button class="text-rose-700 text-xs font-bold"
                                                                type="button"
                                                                wire:click="deleteOverride('permission', {{ (int) $o['id'] }})">
                                                                Hapus
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="py-6 text-center text-gray-400 italic">Belum
                                                    ada permission override</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
