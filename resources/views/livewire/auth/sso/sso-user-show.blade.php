<div class="p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900">Detail SSO User</h2>
            <p class="text-sm text-slate-600 mt-1">Informasi user + identity + roles</p>
        </div>

        <div class="flex gap-2">
            @if ($canRoleAssign)
                <x-ui.sccr-button type="button" class="bg-indigo-600 text-white hover:bg-indigo-700"
                    wire:click="$dispatch('sso-user-open-roles', { userId: {{ (int) $userId }} })">
                    🧩 Roles
                </x-ui.sccr-button>
            @endif

            @if ($canUpdate || $canIdentityUpdate || $canLock)
                <x-ui.sccr-button type="button" class="bg-blue-600 text-white hover:bg-blue-700"
                    wire:click="$dispatch('sso-user-open-edit', { userId: {{ (int) $userId }} })">
                    ✏️ Edit
                </x-ui.sccr-button>
            @endif
        </div>
    </div>

    @if (!$row)
        <div class="mt-6 text-gray-500 italic">User tidak ditemukan.</div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
            <div class="p-4 rounded-xl bg-slate-50 border">
                <div class="text-xs font-bold uppercase text-slate-500">User</div>
                <div class="mt-2 text-sm">
                    <div><b>ID:</b> {{ $row->id }}</div>
                    <div><b>Username:</b> <span class="font-mono">{{ $row->username }}</span></div>
                    <div><b>Email:</b> {{ $row->email ?? '-' }}</div>
                    <div><b>Locked:</b> {{ (int) $row->is_locked === 1 ? 'YES' : 'NO' }}</div>
                    <div><b>Last Login:</b> {{ $row->last_login_at ?? '-' }}</div>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-slate-50 border">
                <div class="text-xs font-bold uppercase text-slate-500">Identity</div>
                <div class="mt-2 text-sm">
                    <div><b>Type:</b> {{ $row->identity_type }}</div>
                    <div><b>Key:</b> <span class="font-mono">{{ $row->identity_key }}</span></div>
                    <div><b>Active:</b> {{ (int) $row->is_active === 1 ? 'YES' : 'NO' }}</div>
                    <div><b>Holding:</b> {{ $row->holding_alias ?? '-' }} — {{ $row->holding_name ?? '-' }}</div>
                    <div><b>Dept/Div:</b> {{ $row->department_name ?? '-' }} / {{ $row->division_name ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="mt-4 p-4 rounded-xl bg-white border">
            <div class="text-xs font-bold uppercase text-slate-500">Roles</div>
            <div class="mt-2 flex flex-wrap gap-2">
                @if (count($roles) === 0)
                    <span class="text-gray-500 italic">-</span>
                @else
                    @foreach ($roles as $r)
                        <span
                            class="px-3 py-1 rounded-full bg-slate-200 text-slate-800 text-xs font-semibold">{{ $r }}</span>
                    @endforeach
                @endif
            </div>
        </div>
    @endif
</div>
