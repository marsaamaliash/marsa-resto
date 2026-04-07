<div class="p-4 sm:p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Role Editor</div>
            <h2 class="text-xl font-extrabold text-gray-900">
                <span class="font-mono">{{ $role['code'] ?? '-' }}</span>
                <span class="text-sm font-semibold text-gray-600">— {{ $role['name'] ?? '' }}</span>
            </h2>
            <div class="text-xs text-gray-500 mt-1">
                Editor ini mengubah <span class="font-mono">auth_role_modules</span> dan <span
                    class="font-mono">auth_role_permissions</span>.
            </div>
        </div>
    </div>

    <div class="mt-4 border rounded-2xl overflow-hidden bg-white shadow-sm" x-data="{ tab: @entangle('tab').live }">
        <div class="px-3 py-2 bg-gray-50 border-b">
            <div class="flex flex-wrap gap-2">
                <button type="button" class="px-3 py-1.5 rounded-full text-xs font-bold"
                    :class="tab === 'modules' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border'"
                    @click="tab='modules'">
                    Modules
                </button>
                <button type="button" class="px-3 py-1.5 rounded-full text-xs font-bold"
                    :class="tab === 'permissions' ? 'bg-gray-900 text-white' : 'bg-white text-gray-700 border'"
                    @click="tab='permissions'">
                    Permissions
                </button>
            </div>
        </div>

        <div class="p-3 sm:p-4 max-h-[70vh] overflow-auto">
            {{-- ============ MODULES TAB ============ --}}
            <div x-show="tab==='modules'" x-cloak>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold text-gray-900">Role Modules (scope + access_level)</div>
                        <div class="text-xs text-gray-500">Jika data panjang, area ini auto-scroll.</div>
                    </div>

                    @if ($canModuleUpdate)
                        <x-ui.sccr-button type="button" variant="success" wire:click="saveModules">
                            Simpan Modules
                        </x-ui.sccr-button>
                    @else
                        <div
                            class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl">
                            Read-only (<span class="font-mono">SSO_ROLE_MODULE_UPDATE</span>)
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex flex-wrap items-end gap-2">
                    <div class="flex-1 min-w-[220px]">
                        <label class="text-xs font-bold text-gray-700">Tambah Module</label>
                        <select wire:model.live="addModuleCode" class="w-full border-gray-300 rounded-lg text-sm mt-1">
                            <option value="">-- pilih module --</option>
                            @foreach ($allModules as $code => $label)
                                <option value="{{ $code }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($canModuleUpdate)
                        <x-ui.sccr-button type="button" variant="primary" wire:click="addModuleRow">
                            Tambah
                        </x-ui.sccr-button>
                    @endif
                </div>

                <div class="mt-4 space-y-2">
                    @forelse($moduleRows as $idx => $row)
                        <div class="p-3 rounded-xl border bg-white">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 font-mono">
                                        {{ $row['module_code'] ?? '-' }}
                                    </div>
                                    <div class="text-[11px] text-gray-500">
                                        {{ $allModules[$row['module_code'] ?? ''] ?? '' }}
                                    </div>
                                </div>

                                @if ($canModuleUpdate)
                                    <x-ui.sccr-button type="button" variant="danger"
                                        wire:click="removeModuleRow({{ $idx }})" class="h-[28px] px-3 text-xs">
                                        Hapus
                                    </x-ui.sccr-button>
                                @endif
                            </div>

                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <label class="text-xs font-bold text-gray-700">Scope Type</label>
                                    <select wire:model.live="moduleRows.{{ $idx }}.scope_type"
                                        class="w-full border-gray-300 rounded-lg text-sm mt-1"
                                        @disabled(!$canModuleUpdate)>
                                        <option value="">global</option>
                                        <option value="holding">holding</option>
                                        <option value="department">department</option>
                                        <option value="division">division</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-gray-700">Access Level</label>
                                    <select wire:model.live="moduleRows.{{ $idx }}.access_level"
                                        class="w-full border-gray-300 rounded-lg text-sm mt-1"
                                        @disabled(!$canModuleUpdate)>
                                        <option value="view">view</option>
                                        <option value="full">full</option>
                                    </select>
                                </div>

                                {{-- scope id fields --}}
                                @php($st = (string) ($row['scope_type'] ?? ''))
                                @if ($st === 'holding')
                                    <div class="sm:col-span-2">
                                        <label class="text-xs font-bold text-gray-700">Holding</label>
                                        <select wire:model.live="moduleRows.{{ $idx }}.scope_holding_id"
                                            class="w-full border-gray-300 rounded-lg text-sm mt-1"
                                            @disabled(!$canModuleUpdate)>
                                            <option value="">-- pilih holding --</option>
                                            @foreach ($holdingOptions as $id => $label)
                                                <option value="{{ $id }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif($st === 'department')
                                    <div class="sm:col-span-2">
                                        <label class="text-xs font-bold text-gray-700">Department</label>
                                        <select wire:model.live="moduleRows.{{ $idx }}.scope_department_id"
                                            class="w-full border-gray-300 rounded-lg text-sm mt-1"
                                            @disabled(!$canModuleUpdate)>
                                            <option value="">-- pilih department --</option>
                                            @foreach ($departmentOptions as $id => $label)
                                                <option value="{{ $id }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @elseif($st === 'division')
                                    <div class="sm:col-span-2">
                                        <label class="text-xs font-bold text-gray-700">Division</label>
                                        <select wire:model.live="moduleRows.{{ $idx }}.scope_division_id"
                                            class="w-full border-gray-300 rounded-lg text-sm mt-1"
                                            @disabled(!$canModuleUpdate)>
                                            <option value="">-- pilih division --</option>
                                            @foreach ($divisionOptions as $id => $label)
                                                <option value="{{ $id }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <div class="sm:col-span-2 flex items-center gap-2">
                                    <label class="text-xs font-bold text-gray-700">Active</label>
                                    <input type="checkbox" wire:model.live="moduleRows.{{ $idx }}.is_active"
                                        class="rounded border-gray-300" @disabled(!$canModuleUpdate)>
                                    <span class="text-xs text-gray-500">aktifkan akses module ini</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400 italic">Belum ada module mapping untuk role ini.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- ============ PERMISSIONS TAB ============ --}}
            <div x-show="tab==='permissions'" x-cloak>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-bold text-gray-900">Role Permissions</div>
                        <div class="text-xs text-gray-500">Checklist permission per module.</div>
                    </div>

                    @if ($canPermissionUpdate)
                        <x-ui.sccr-button type="button" variant="success" wire:click="savePermissions">
                            Simpan Permissions
                        </x-ui.sccr-button>
                    @else
                        <div
                            class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded-xl">
                            Read-only (<span class="font-mono">SSO_ROLE_PERMISSION_UPDATE</span>)
                        </div>
                    @endif
                </div>

                <div class="mt-4 space-y-3">
                    @forelse($permissionsGrouped as $grp)
                        <div class="rounded-xl border bg-white overflow-hidden">
                            <div class="px-3 py-2 bg-gray-50 border-b">
                                <div class="text-sm font-bold text-gray-900">
                                    <span class="font-mono">{{ $grp['module_code'] }}</span> ·
                                    {{ $grp['module_name'] }}
                                </div>
                            </div>

                            <div class="p-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($grp['items'] as $p)
                                    <label class="flex items-start gap-3 p-2 rounded-lg border hover:bg-gray-50">
                                        <input type="checkbox" value="{{ (int) $p['id'] }}"
                                            wire:model.live="selectedPermissionIds"
                                            class="rounded border-gray-300 mt-1" @disabled(!$canPermissionUpdate)>
                                        <div class="min-w-0">
                                            <div class="text-xs font-mono font-bold text-gray-900">
                                                {{ $p['code'] }}
                                                @if ((int) $p['requires_approval'] === 1)
                                                    <span
                                                        class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-900">approval</span>
                                                @endif
                                            </div>
                                            @if (!empty($p['description']))
                                                <div class="text-[11px] text-gray-500">{{ $p['description'] }}</div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-gray-400 italic">Tidak ada permission</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
