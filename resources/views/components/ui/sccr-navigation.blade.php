{{-- Navigasi Putih --}}
{{-- <nav x-data="{ open: false }" class="bg-white w-full relative m-0 p-0 border-none"> --}}
<nav x-data="{ open: false }" class="w-full relative m-0 p-0 border-none">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">

            {{-- LOGO (AMAN UNTUK GUEST & AUTH) --}}
            <div class="flex items-center gap-2">
                @auth
                    <button type="button"
                        class="inline-flex items-center justify-center w-12 h-12 rounded-lg border border-gray-200 bg-white hover:bg-gray-50"
                        title="Toggle menu" x-on:click="window.dispatchEvent(new CustomEvent('sccr-sidebar-toggle'))">
                        {{-- <span class="text-lg leading-none">☰</span> --}}
                        <span class="text-lg leading-none">📲 </span>
                    </button>
                @endauth

                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/logoSCCR.png') }}" alt="Logo" class="h-10">
                    <x-ui.sccr-company-name name="Stem Cell and Cancer Research Indonesia" />
                    {{-- <x-ui.sccr-company-name name="Company Name" /> --}}
                </a>
            </div>

            {{-- =========================
                 AUTHENTICATED USER AREA
                 ========================= --}}
            @auth
                @php
                    $hour = now()->format('H');
                    if ($hour < 12) {
                        $greeting = 'Good Morning';
                    } elseif ($hour < 17) {
                        $greeting = 'Good Afternoon';
                    } elseif ($hour < 20) {
                        $greeting = 'Good Evening';
                    } else {
                        $greeting = 'Good Night';
                    }

                    $authUser = auth()->user();
                    $identity = $authUser->identity ?? null;
                    $profile = $identity ? \App\Services\AuthProfileResolver::resolve($identity) : null;

                    $nip = $identity?->identity_key ?? 'default';
                    $fotoPath = public_path("photo/employee/{$nip}.png");
                    $gender = $profile?->jenis_kelamin ?? 'Laki-laki';
                @endphp

                <div class="flex items-center space-x-3">

                    {{-- Foto --}}
                    <img src="{{ asset('photo/employee/' . $nip . '.png') }}"
                        class="w-10 h-10 rounded-full object-cover border border-gray-300 shadow-sm"
                        onerror="this.onerror=null;this.src='{{ asset($gender === 'Perempuan' ? 'photo/woman.png' : 'photo/man.png') }}';" />

                    {{-- Greeting --}}
                    {{-- <span class="text-gray-700 text-sm leading-tight">
                        {{ $greeting }}, <br>
                        @if ($profile)
                            {{ $profile->gelar_depan ?? '' }}
                            <strong>{{ $profile->nama ?? ($profile->nama_lengkap ?? '-') }}</strong>
                            {{ $profile->gelar_belakang ? ', ' . $profile->gelar_belakang : '' }}
                        @else
                            <strong>User</strong>
                        @endif
                    </span> --}}

                    {{-- Greeting --}}
                    @php
                        $displayName = null;

                        if ($profile) {
                            $nama = $profile->nama ?? ($profile->nama_lengkap ?? null);
                            $gd = $profile->gelar_depan ?? '';
                            $gb = $profile->gelar_belakang ?? '';
                            $displayName = trim($gd . ' ' . $nama) . ($gb ? ', ' . $gb : '');
                        }

                        // fallback berurutan: profile -> username -> identity_key
                        $displayName =
                            $displayName ?: $authUser->username ?? null ?: $identity?->identity_key ?? 'User';
                    @endphp

                    <span class="text-gray-700 text-sm leading-tight">
                        {{ $greeting }}, <br>
                        <strong>{{ $displayName }}</strong>
                    </span>

                    {{-- Dropdown --}}
                    <x-ui.sccr-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-1 py-2 text-sm font-medium text-gray-600 bg-white hover:text-gray-800 focus:outline-none">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @module('00000')
                                @permission('SSO_ROLE_VIEW')
                                    <x-ui.sccr-dropdown-link :href="route('sso.roles.table')">
                                        Roles & Access
                                    </x-ui.sccr-dropdown-link>
                                @endpermission
                            @endmodule

                            @module('00000')
                                @permission('SSO_USER_VIEW')
                                    <x-ui.sccr-dropdown-link :href="route('sso.users.table')">
                                        SSO Users
                                    </x-ui.sccr-dropdown-link>
                                @endpermission
                            @endmodule

                            @module('00000')
                                @permission('APPROVAL_VIEW')
                                    <x-ui.sccr-dropdown-link :href="route('sso.approvals.inbox')">
                                        Approval Inbox
                                    </x-ui.sccr-dropdown-link>
                                @endpermission
                            @endmodule

                            @module('00000')
                                @permission('SSO_NAV_VIEW')
                                    <x-ui.sccr-dropdown-link :href="route('sso.nav-items.table')">
                                        Menu Editor
                                    </x-ui.sccr-dropdown-link>
                                @endpermission
                            @endmodule

                            <hr class="my-2 border-gray-200">

                            <x-ui.sccr-dropdown-link :href="route('profile.edit') . '#security'">
                                Change Password
                            </x-ui.sccr-dropdown-link>

                            <x-ui.sccr-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-ui.sccr-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-ui.sccr-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-ui.sccr-dropdown-link>
                            </form>
                        </x-slot>
                    </x-ui.sccr-dropdown>

                    {{-- Mobile Toggle --}}
                    <div class="md:hidden flex items-center">
                        <button @click="open = !open"
                            class="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endauth

            {{-- =========================
                 GUEST AREA
                 ========================= --}}
            @guest
                <div class="text-sm text-gray-500 italic">
                    Silakan login
                </div>
            @endguest

        </div>
    </div>

    {{-- MOBILE MENU (AUTH ONLY) --}}
    @auth
        <div :class="{ 'block': open, 'hidden': !open }" class="hidden md:hidden bg-white border-b">
            <div class="pt-2 pb-3 space-y-1">
                <x-sccr-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-sccr-responsive-nav-link>
            </div>
        </div>
    @endauth
</nav>
