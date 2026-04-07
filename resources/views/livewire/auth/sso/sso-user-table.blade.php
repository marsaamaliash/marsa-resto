{{-- resources/views/livewire/auth/sso/sso-user-table.blade.php --}}
<x-ui.sccr-card transparent wire:key="sso-user-table" class="h-full min-h-0 flex flex-col">

    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-slate-800/90 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-white">SSO Users</h1>
                <p class="text-slate-200 text-sm">Kelola akun login, identity scope, dan role assignment</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
            <div class="text-white">
                Total <span class="font-bold text-yellow-300">{{ $rows->total() }}</span> user 👤
            </div>
        </div>
    </div>

    {{-- ================= FILTERS & ACTIONS ================= --}}
    <div class="px-4 pt-3 pb-1">
        <div class="flex flex-wrap items-center justify-between gap-1">

            <form wire:submit.prevent="applyFilter" class="flex flex-wrap items-center gap-1 flex-grow">

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">
                        Cari username / email / identity
                    </span>
                    <x-ui.sccr-input name="search" wire:model="search" placeholder="Ketik lalu enter..."
                        class="w-72" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Type</span>
                    <x-ui.sccr-select name="filterType" wire:model.live="filterType" :options="[
                        '' => 'Semua',
                        'employee' => 'employee',
                        'lecturer' => 'lecturer',
                        'student' => 'student',
                    ]" class="w-36" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Holding</span>
                    <x-ui.sccr-select name="filterHolding" wire:model.live="filterHolding" :options="['' => 'Semua'] + $holdingOptions"
                        class="w-48" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Active</span>
                    <x-ui.sccr-select name="filterActive" wire:model.live="filterActive" :options="['' => 'Semua', '1' => 'Active', '0' => 'Inactive']"
                        class="w-28" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Locked</span>
                    <x-ui.sccr-select name="filterLocked" wire:model.live="filterLocked" :options="['' => 'Semua', '1' => 'Locked', '0' => 'Unlocked']"
                        class="w-32" />
                </div>

                <div class="relative top-1">
                    <span class="absolute -top-3 left-1 text-[10px] font-bold text-black uppercase">Role</span>
                    <x-ui.sccr-select name="filterRole" wire:model.live="filterRole" :options="['' => 'Semua'] + $roleOptions" class="w-52" />
                </div>

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

                    @if ($canExport)
                        <x-ui.sccr-button type="button" wire:click="exportFiltered"
                            class="bg-gray-600 text-gray-100 hover:bg-gray-400">
                            <x-ui.sccr-icon name="exportfiltered" :size="20" />
                            Export Filtered
                        </x-ui.sccr-button>

                        <x-ui.sccr-button type="button" wire:click="exportSelected"
                            class="bg-gray-500 text-gray-900 hover:bg-gray-400" :disabled="count($selected) === 0">
                            <x-ui.sccr-icon name="exportselected" :size="20" />
                            Export Selected ({{ count($selected) }})
                        </x-ui.sccr-button>
                    @endif

                    @if ($canDeactivateRequest)
                        <x-ui.sccr-button type="button" wire:click="openDeactivateRequestSelected"
                            class="bg-amber-600/80 hover:bg-amber-700 text-white" :disabled="count($selected) === 0">
                            <span class="inline-flex items-center gap-2">
                                <span class="text-lg">🧾</span>
                                Request Deactivate ({{ count($selected) }})
                            </span>
                        </x-ui.sccr-button>
                    @endif

                    @if ($canCloneAccess)
                        <x-ui.sccr-button type="button" wire:click="openCloneAccessSelected"
                            class="bg-indigo-700 text-white hover:bg-indigo-800" :disabled="count($selected) === 0">
                            🧬 Clone Access ({{ count($selected) }})
                        </x-ui.sccr-button>
                    @endif

                </div>
            </form>

            {{-- Right: perpage & create --}}
            <div class="flex items-end gap-1 ml-auto">
                <div class="relative top-0">
                    <span class="absolute -top-4 left-1 text-[10px] font-bold text-black uppercase">Show</span>
                    <select wire:model.live="perPage" class="border-gray-300 rounded-md text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                @if ($canCreate)
                    <x-ui.sccr-button type="button" variant="icon-circle" wire:click="openCreate"
                        class="w-10 h-10 bg-slate-900 text-white hover:bg-slate-700" title="Create/Link SSO User">
                        <x-ui.sccr-icon name="plus" :size="18" />
                    </x-ui.sccr-button>
                @endif
            </div>
        </div>
    </div>

    {{-- ================= TABLE (SCROLL AREA) ================= --}}
    <div class="flex-1 min-h-0 px-4 pb-2">
        <div class="h-full min-h-0 rounded-xl shadow border bg-white overflow-hidden flex flex-col">

            <div class="flex-1 min-h-0 overflow-auto">
                <table class="min-w-full divide-y divide-gray-900">
                    <thead class="bg-slate-700/90 text-white sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                            </th>

                            <th wire:click="sortBy('username')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Username {!! $sortField === 'username' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('email')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Email {!! $sortField === 'email' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('identity_type')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Identity {!! $sortField === 'identity_type' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-bold">Scope</th>

                            <th class="px-4 py-3 text-left text-xs font-bold">Roles</th>

                            <th wire:click="sortBy('is_active')"
                                class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                Active {!! $sortField === 'is_active' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('is_locked')"
                                class="px-4 py-3 text-center text-xs font-bold cursor-pointer">
                                Locked {!! $sortField === 'is_locked' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th wire:click="sortBy('last_login_at')"
                                class="px-4 py-3 text-left text-xs font-bold cursor-pointer">
                                Last Login {!! $sortField === 'last_login_at' ? ($sortDirection === 'asc' ? '▲' : '▼') : '↕' !!}
                            </th>

                            <th class="px-4 py-3 text-center text-xs font-bold">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 bg-gray-100">
                        @forelse ($rows as $u)
                            <tr class="hover:bg-gray-200 transition">
                                <td class="px-4 py-2 text-center">
                                    <input type="checkbox" value="{{ $u->id }}" wire:model.live="selected"
                                        class="rounded border-gray-300">
                                </td>

                                <td class="px-4 py-2 text-sm font-semibold font-mono">
                                    {{ $u->username }}
                                    @if ((int) ($u->pending_deactivate ?? 0) > 0)
                                        <div class="text-[11px] text-amber-700 font-semibold">pending deactivate</div>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-sm">
                                    {{ $u->email ?? '-' }}
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <div class="font-semibold">{{ $u->identity_type }}</div>
                                    <div class="text-gray-600 font-mono">{{ $u->identity_key }}</div>
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    <div class="font-semibold">{{ $u->holding_alias ?? '-' }}</div>
                                    <div class="text-gray-600">{{ $u->holding_name ?? '-' }}</div>
                                    <div class="text-gray-500">
                                        {{ $u->department_name ?? '-' }} · {{ $u->division_name ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    @php($rs = $rolesMap[(int) $u->id] ?? [])
                                    @if (count($rs) === 0)
                                        <span class="text-gray-500 italic">-</span>
                                    @else
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($rs as $r)
                                                <span
                                                    class="px-2 py-0.5 rounded-full text-[11px] bg-slate-200 text-slate-800 font-semibold">
                                                    {{ $r['code'] }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <x-ui.sccr-badge :type="(int) $u->is_active === 1 ? 'success' : 'danger'">
                                        {{ (int) $u->is_active === 1 ? 'Active' : 'Inactive' }}
                                    </x-ui.sccr-badge>
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <x-ui.sccr-badge :type="(int) $u->is_locked === 1 ? 'danger' : 'info'">
                                        {{ (int) $u->is_locked === 1 ? 'Locked' : 'OK' }}
                                    </x-ui.sccr-badge>
                                </td>

                                <td class="px-4 py-2 text-xs">
                                    {{ $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->format('Y-m-d H:i') : '-' }}
                                </td>

                                <td class="px-4 py-2 text-center">
                                    <div class="flex justify-center gap-3">
                                        <x-ui.sccr-button type="button" variant="icon"
                                            wire:click="openShow({{ (int) $u->id }})"
                                            class="text-gray-700 hover:scale-125" title="Detail">
                                            <x-ui.sccr-icon name="eye" :size="20" />
                                        </x-ui.sccr-button>

                                        @if ($canUpdate || $canIdentityUpdate || $canLock)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openEdit({{ (int) $u->id }})"
                                                class="text-blue-600 hover:scale-125" title="Edit">
                                                <x-ui.sccr-icon name="edit" :size="20" />
                                            </x-ui.sccr-button>
                                        @endif

                                        @if ($canRoleAssign)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openRoles({{ (int) $u->id }})"
                                                class="text-indigo-600 hover:scale-125" title="Roles">
                                                <span class="text-[18px] leading-none">🧩</span>
                                            </x-ui.sccr-button>
                                        @endif

                                        @if ($canRoleAssign)
                                            {{-- 🔐 Access (view + edit roles + effective access) --}}
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openAccess({{ (int) $u->id }})"
                                                class="text-slate-900 hover:scale-125"
                                                title="Access (Roles / Modules / Permissions)">
                                                <span class="text-[18px] leading-none">🔐</span>
                                            </x-ui.sccr-button>
                                        @endif

                                        @if ($canLock)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="toggleLock({{ (int) $u->id }})"
                                                class="text-slate-900 hover:scale-125" title="Lock/Unlock">
                                                <span class="text-[18px] leading-none">
                                                    {{ (int) $u->is_locked === 1 ? '🔓' : '🔒' }}
                                                </span>
                                            </x-ui.sccr-button>
                                        @endif

                                        {{-- ✅ Reset Password: normal direct confirm modal, super admin via approval --}}
                                        @if ($canPasswordResetDirect || $canPasswordResetRequest)
                                            @if ((int) ($u->is_super_admin ?? 0) === 1)
                                                @if ($canPasswordResetRequest)
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="openResetPasswordApproval({{ (int) $u->id }})"
                                                        class="text-rose-700 hover:scale-125"
                                                        title="Reset password super admin (wajib approval DEV)">
                                                        <span class="text-[18px] leading-none">🛡️🔑</span>
                                                    </x-ui.sccr-button>
                                                @endif
                                            @else
                                                @if ($canPasswordResetDirect)
                                                    <x-ui.sccr-button type="button" variant="icon"
                                                        wire:click="openResetPassword({{ (int) $u->id }})"
                                                        class="text-rose-700 hover:scale-125"
                                                        title="Reset password ke default password123">
                                                        <span class="text-[18px] leading-none">🔑</span>
                                                    </x-ui.sccr-button>
                                                @endif
                                            @endif
                                        @endif

                                        @if ($canDeactivateRequest)
                                            <x-ui.sccr-button type="button" variant="icon"
                                                wire:click="openDeactivateRequestSingle({{ (int) $u->id }})"
                                                class="text-amber-700 hover:scale-125"
                                                title="Request Deactivate (Approval)">
                                                <span class="text-[18px] leading-none">⁉️</span>
                                            </x-ui.sccr-button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-gray-400 italic">Data tidak ditemukan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- ============ DEACTIVATE REQUEST MODAL ============ --}}
                @if ($showConfirmModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Request Deactivate (Approval)</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Permintaan akan masuk ke antrian approval. Tidak langsung menonaktifkan user.
                                    </p>
                                </div>

                                <x-ui.sccr-button type="button" variant="icon" wire:click="cancelDeactivateRequest"
                                    class="text-gray-500 hover:text-gray-800" title="Tutup">
                                    <span class="text-xl leading-none">×</span>
                                </x-ui.sccr-button>
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-bold text-gray-700">Alasan</label>
                                <textarea wire:model.live="reason" class="w-full border-gray-300 rounded-lg text-sm mt-1" rows="3"
                                    placeholder="Contoh: resign / mutasi / akun tidak valid"></textarea>
                                <div class="text-[11px] text-gray-500 mt-1">Maks 255 karakter.</div>
                            </div>

                            <div class="mt-4 text-xs text-gray-700">
                                @if ($isBulk)
                                    <div>Target: <b>{{ count($selected) }}</b> user terpilih</div>
                                @else
                                    <div>Target User ID: <b>{{ $confirmingUserId }}</b></div>
                                @endif
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-ui.sccr-button type="button" variant="secondary"
                                    wire:click="cancelDeactivateRequest">
                                    Batal
                                </x-ui.sccr-button>
                                <x-ui.sccr-button type="button" variant="danger"
                                    wire:click="submitDeactivateRequest">
                                    Kirim Request
                                </x-ui.sccr-button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ============ RESET PASSWORD CONFIRM MODAL (DIRECT) ============ --}}
                @if ($showResetConfirmModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Reset Password</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Password akan direset menjadi <b>password123</b> dan user wajib ganti password
                                        saat login.
                                    </p>
                                </div>

                                <x-ui.sccr-button type="button" variant="icon" wire:click="cancelResetPassword"
                                    class="text-gray-500 hover:text-gray-800" title="Tutup">
                                    <span class="text-xl leading-none">×</span>
                                </x-ui.sccr-button>
                            </div>

                            <div class="mt-4 text-sm text-gray-700">
                                Target: <b>{{ $resetTargetLabel }}</b>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-ui.sccr-button type="button" variant="secondary"
                                    wire:click="cancelResetPassword">
                                    Batal
                                </x-ui.sccr-button>

                                <x-ui.sccr-button type="button" variant="danger" wire:click="confirmResetPassword">
                                    Ya, Reset Password
                                </x-ui.sccr-button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ============ RESET PASSWORD SUPER ADMIN (APPROVAL) ============ --}}
                @if ($showResetApprovalModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Request Reset Password (Approval)</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Khusus <b>super admin</b> wajib approval oleh <b>DEV</b>.
                                    </p>
                                </div>

                                <x-ui.sccr-button type="button" variant="icon"
                                    wire:click="cancelResetPasswordApproval" class="text-gray-500 hover:text-gray-800"
                                    title="Tutup">
                                    <span class="text-xl leading-none">×</span>
                                </x-ui.sccr-button>
                            </div>

                            <div class="mt-4">
                                <label class="text-sm font-bold text-gray-700">Alasan Reset</label>
                                <textarea wire:model.live="resetApprovalReason" class="w-full border-gray-300 rounded-lg text-sm mt-1" rows="3"
                                    placeholder="Contoh: lupa password, akses recovery, reset device, dll"></textarea>
                                <div class="text-[11px] text-gray-500 mt-1">Maks 255 karakter.</div>
                            </div>

                            <div class="mt-4 text-xs text-gray-700">
                                Target User ID: <b>{{ $resetApprovalTargetUserId }}</b>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-ui.sccr-button type="button" variant="secondary"
                                    wire:click="cancelResetPasswordApproval">
                                    Batal
                                </x-ui.sccr-button>
                                <x-ui.sccr-button type="button" variant="danger"
                                    wire:click="submitResetPasswordApproval">
                                    Kirim Request
                                </x-ui.sccr-button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ============ BULK CLONE ACCESS MODAL (UPDATED) ============ --}}
                @if ($showCloneModal)
                    @php($preset = $clonePreset ?? 'all')
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800">Bulk Clone Access</h3>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Copy hak akses dari 1 user template ke <b>{{ count($selected) }}</b> user
                                        terpilih.
                                        Identity scope target tidak berubah.
                                    </p>
                                </div>

                                <x-ui.sccr-button type="button" variant="icon" wire:click="cancelCloneAccess"
                                    class="text-gray-500 hover:text-gray-800" title="Tutup">
                                    <span class="text-xl leading-none">×</span>
                                </x-ui.sccr-button>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">

                                <div class="md:col-span-2">
                                    <label class="text-sm font-bold text-gray-700">Source User (Template)</label>
                                    <select wire:model.defer="cloneFromUserId"
                                        class="w-full border-gray-300 rounded-lg text-sm mt-1">
                                        <option value="">-- pilih user template --</option>
                                        @foreach ($cloneUserOptions as $id => $label)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-gray-700">Clone Preset</label>
                                    <select wire:model.live="clonePreset"
                                        class="w-full border-gray-300 rounded-lg text-sm mt-1">
                                        <option value="all">ALL (Roles + Overrides)</option>
                                        <option value="roles_only">Roles Only</option>
                                        <option value="overrides_only">Overrides Only</option>
                                    </select>
                                    <div class="text-[11px] text-gray-500 mt-1">
                                        Preset akan mengunci checkbox agar konsisten (best practice).
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-bold text-gray-700">Scope Strategy</label>
                                    <select wire:model.live="cloneScopeStrategy"
                                        class="w-full border-gray-300 rounded-lg text-sm mt-1">
                                        <option value="rebase_to_target">REBASE to Target Scope (recommended)</option>
                                        <option value="keep">KEEP source scope (raw)</option>
                                        <option value="to_global">FORCE Global</option>
                                    </select>
                                    <div class="text-[11px] text-gray-500 mt-1">
                                        Untuk clone lintas holding / lintas identity_type, gunakan <b>REBASE</b>.
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="text-sm font-bold text-gray-700">Mode</label>
                                    <select wire:model.defer="cloneMode"
                                        class="w-full border-gray-300 rounded-lg text-sm mt-1">
                                        <option value="replace">REPLACE (hapus akses target lalu copy)</option>
                                        <option value="merge">MERGE (gabung + update yang sama)</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2 flex items-center gap-3">
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model.live="cloneOnlyActiveOverrides"
                                            class="rounded border-gray-300">
                                        <span>Copy hanya override Active</span>
                                    </label>
                                </div>

                                <div class="md:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model.live="cloneRoles"
                                            class="rounded border-gray-300"
                                            {{ $preset === 'overrides_only' ? 'disabled' : '' }}>
                                        <span
                                            class="{{ $preset === 'overrides_only' ? 'text-gray-400' : '' }}">Roles</span>
                                    </label>

                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model.live="cloneModuleOverrides"
                                            class="rounded border-gray-300"
                                            {{ $preset === 'roles_only' ? 'disabled' : '' }}>
                                        <span class="{{ $preset === 'roles_only' ? 'text-gray-400' : '' }}">Module
                                            Overrides</span>
                                    </label>

                                    <label class="inline-flex items-center gap-2 text-sm">
                                        <input type="checkbox" wire:model.live="clonePermissionOverrides"
                                            class="rounded border-gray-300"
                                            {{ $preset === 'roles_only' ? 'disabled' : '' }}>
                                        <span class="{{ $preset === 'roles_only' ? 'text-gray-400' : '' }}">Permission
                                            Overrides</span>
                                    </label>
                                </div>

                                <div
                                    class="md:col-span-2 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                    <b>Catatan penting (scope):</b>
                                    <ul class="list-disc ml-5 mt-1 space-y-1">
                                        <li>
                                            Jika role/module scoped (department/division), target hanya “melihat” module
                                            jika scope-nya match.
                                        </li>
                                        <li>
                                            Untuk clone antar holding/antar identity_type (mis. employee → lecturer),
                                            gunakan
                                            <b>Scope Strategy: REBASE</b> agar override scope otomatis ikut scope
                                            target.
                                        </li>
                                        <li>
                                            Preset <b>Roles Only</b> cocok untuk “kembar role”, tetapi efektivitas tetap
                                            mengikuti scope rules di role_modules.
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <x-ui.sccr-button type="button" variant="secondary" wire:click="cancelCloneAccess">
                                    Batal
                                </x-ui.sccr-button>

                                <x-ui.sccr-button type="button" variant="primary" wire:click="submitCloneAccess"
                                    class="bg-indigo-700 text-white hover:bg-indigo-800">
                                    Jalankan Bulk Clone
                                </x-ui.sccr-button>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- FOOTER (pagination) --}}
            <div
                class="flex-none px-6 py-3 border-t bg-white flex flex-col md:flex-row justify-between items-center gap-3">
                <div class="text-sm text-gray-600 flex items-center">
                    <span class="font-bold text-gray-800 mr-1">{{ count($selected) }}</span> user dipilih
                    @if ($canDeactivateRequest && count($selected) > 0)
                        <x-ui.sccr-button type="button" wire:click="openDeactivateRequestSelected"
                            class="ml-4 h-[30px] px-3 text-xs bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200">
                            🧾 Request Deactivate Terpilih
                        </x-ui.sccr-button>
                    @endif
                </div>
                <div>{{ $rows->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ================= TOAST ================= --}}
    <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']" wire:key="toast-{{ microtime() }}" />

    {{-- ================= OVERLAYS ================= --}}
    @if ($overlayMode === 'create')
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>
                <livewire:auth.sso.sso-user-create wire:key="sso-create" />
            </div>
        </div>
    @endif

    @if ($overlayMode === 'show' && $overlayUserId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>
                <livewire:auth.sso.sso-user-show :userId="$overlayUserId" wire:key="sso-show-{{ $overlayUserId }}" />
            </div>
        </div>
    @endif

    @if ($overlayMode === 'edit' && $overlayUserId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-5xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>
                <livewire:auth.sso.sso-user-edit :userId="$overlayUserId" wire:key="sso-edit-{{ $overlayUserId }}" />
            </div>
        </div>
    @endif

    @if ($overlayMode === 'roles' && $overlayUserId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-6">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl relative">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>
                <livewire:auth.sso.sso-user-roles :userId="$overlayUserId" wire:key="sso-roles-{{ $overlayUserId }}" />
            </div>
        </div>
    @endif

    @if ($overlayMode === 'access' && $overlayUserId)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeOverlay"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4 sm:px-6">
            <div class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl relative overflow-hidden">
                <x-ui.sccr-button type="button" variant="icon" wire:click="closeOverlay"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 z-10" title="Tutup">
                    <span class="text-xl leading-none">✕</span>
                </x-ui.sccr-button>

                <livewire:auth.sso.sso-user-access-overlay :userId="$overlayUserId"
                    wire:key="sso-access-{{ $overlayUserId }}" />
            </div>
        </div>
    @endif

</x-ui.sccr-card>
