<div class="h-full min-h-0">
    {{-- ================= HEADER ================= --}}
    <div class="relative px-8 py-6 bg-yellow-500/60 rounded-b-3xl shadow-lg overflow-hidden">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">SSO User Access</h1>
                <p class="text-lg text-gray-800">Silakan pilih modul yang ingin diakses</p>
            </div>
        </div>

        <div class="mt-4 flex justify-between items-center text-sm">
            <x-ui.sccr-breadcrumb :items="$breadcrumbs" />
        </div>
    </div>

    <div class="fixed top-0 left-0 w-full h-full z-0 pointer-events-none">
        <img src="{{ asset('images/bg-gedung.jpg') }}" alt="Background" class="w-full h-full object-cover opacity-30">
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- ================= SSO USERS ================= --}}
            @permission('SSO_USER_VIEW')
                <a href="{{ route('sso.users.table') }}"
                    class="group rounded-2xl bg-white/90 shadow-lg hover:shadow-xl border border-white/30 overflow-hidden transform hover:scale-[1.02] transition">
                    <div class="p-5 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-900 text-white flex items-center justify-center text-2xl">
                            👤
                        </div>

                        <div class="flex-1">
                            <div class="text-lg font-bold text-gray-900">Manage SSO Users</div>
                            <div class="text-sm text-gray-600 mt-1">
                                CRUD user SSO (auth_users + auth_identities) + role assignment.
                            </div>

                            <div class="mt-3 text-xs text-gray-500">
                                Path UI: <span class="font-mono">livewire/auth/sso</span>
                            </div>
                        </div>

                        <div class="text-gray-400 group-hover:text-gray-700 transition">➜</div>
                    </div>
                </a>
            @else
                <div class="rounded-2xl bg-white/60 shadow border border-white/30 p-5 opacity-60">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-300 text-white flex items-center justify-center text-2xl">
                            👤
                        </div>
                        <div class="flex-1">
                            <div class="text-lg font-bold text-gray-700">Manage SSO Users</div>
                            <div class="text-sm text-gray-600 mt-1">
                                Tidak punya izin: <span class="font-mono">SSO_USER_VIEW</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endpermission

            {{-- ================= SSO ROLES & ACCESS (NEW) ================= --}}
            @permission('SSO_ROLE_VIEW')
                <a href="{{ route('sso.roles.table') }}"
                    class="group rounded-2xl bg-white/90 shadow-lg hover:shadow-xl border border-white/30 overflow-hidden transform hover:scale-[1.02] transition">
                    <div class="p-5 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-slate-800 text-white flex items-center justify-center text-2xl">
                            🧩
                        </div>

                        <div class="flex-1">
                            <div class="text-lg font-bold text-gray-900">Roles & Access</div>
                            <div class="text-sm text-gray-600 mt-1">
                                Kelola Role + editor <b>Module Access</b> & <b>Permissions</b> (overlay, autoscroll).
                            </div>

                            <div class="mt-3 text-xs text-gray-500">
                                Edit ACL: <span class="font-mono">auth_role_modules</span> & <span
                                    class="font-mono">auth_role_permissions</span>
                            </div>
                        </div>

                        <div class="text-gray-400 group-hover:text-gray-700 transition">➜</div>
                    </div>
                </a>
            @else
                <div class="rounded-2xl bg-white/60 shadow border border-white/30 p-5 opacity-60">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-300 text-white flex items-center justify-center text-2xl">
                            🧩
                        </div>
                        <div class="flex-1">
                            <div class="text-lg font-bold text-gray-700">Roles & Access</div>
                            <div class="text-sm text-gray-600 mt-1">
                                Tidak punya izin: <span class="font-mono">SSO_ROLE_VIEW</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endpermission

            {{-- ================= APPROVAL INBOX ================= --}}
            @permission('APPROVAL_VIEW')
                <a href="{{ route('sso.approvals.inbox') }}"
                    class="group rounded-2xl bg-white/90 shadow-lg hover:shadow-xl border border-white/30 overflow-hidden transform hover:scale-[1.02] transition">
                    <div class="p-5 flex items-start gap-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-emerald-700 text-white flex items-center justify-center text-2xl">
                            ✅
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="text-lg font-bold text-gray-900">Approval Inbox</div>

                                @if (!empty($pendingApprovals))
                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-900">
                                        Pending: {{ $pendingApprovals }}
                                    </span>
                                @endif
                            </div>

                            <div class="text-sm text-gray-600 mt-1">
                                Approve / Reject semua request di <span class="font-mono">auth_approvals</span>.
                            </div>

                            <div class="mt-3 text-xs text-gray-500">
                                Scope mengikuti role_modules (division/department/global).
                            </div>
                        </div>

                        <div class="text-gray-400 group-hover:text-gray-700 transition">➜</div>
                    </div>
                </a>
            @else
                <div class="rounded-2xl bg-white/60 shadow border border-white/30 p-5 opacity-60">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-300 text-white flex items-center justify-center text-2xl">
                            ✅
                        </div>
                        <div class="flex-1">
                            <div class="text-lg font-bold text-gray-700">Approval Inbox</div>
                            <div class="text-sm text-gray-600 mt-1">
                                Tidak punya izin: <span class="font-mono">APPROVAL_VIEW</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endpermission

            @permission('SSO_NAV_VIEW')
                <a href="{{ route('sso.nav-items.table') }}"
                    class="group rounded-2xl bg-white/90 shadow-lg hover:shadow-xl border border-white/30 overflow-hidden transform hover:scale-[1.02] transition">
                    <div class="p-5 flex items-start gap-4">
                        <div
                            class="w-12 h-12 rounded-xl bg-indigo-700 text-white flex items-center justify-center text-2xl">
                            🧭</div>
                        <div class="flex-1">
                            <div class="text-lg font-bold text-gray-900">Menu Editor</div>
                            <div class="text-sm text-gray-600 mt-1">CRUD <span class="font-mono">auth_nav_items</span>.
                            </div>
                            <div class="mt-3 text-xs text-gray-500">Tambah submenu tanpa SQL manual.</div>
                        </div>
                        <div class="text-gray-400 group-hover:text-gray-700 transition">➜</div>
                    </div>
                </a>
            @endpermission
        </div>
    </div>
</div>
