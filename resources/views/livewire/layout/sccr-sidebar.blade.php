<aside class="h-full min-h-0 w-72 bg-white shadow-lg border-r border-gray-200">
    <div class="h-full min-h-0 flex flex-col">

        {{-- HEADER --}}
        <div class="flex-none p-3 border-b border-gray-200">
            <div class="flex items-center justify-between gap-2">
                <div class="min-w-0">
                    <div class="text-sm font-extrabold text-gray-800">Menu</div>
                    <div class="text-[11px] text-gray-500">ERP Navigation</div>
                </div>

                <button type="button"
                    class="w-10 h-10 rounded-lg border border-gray-200 hover:bg-gray-50 flex items-center justify-center"
                    title="Hide sidebar" x-on:click="window.dispatchEvent(new CustomEvent('sccr-sidebar-close'))">
                    <span class="text-lg">✕</span>
                </button>
            </div>

            {{-- GO TO --}}
            <form wire:submit.prevent="goModule" class="mt-3">
                <label class="text-[10px] font-bold uppercase text-gray-500">Go to (Module Code)</label>
                <div class="mt-1 flex gap-2">
                    <input wire:model.defer="go" class="w-full rounded-lg border-gray-300 text-sm px-3 py-2"
                        placeholder="01005 / 00000 / SSO-ROLES" />
                    <button type="submit"
                        class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                        Go
                    </button>
                </div>
                <div class="mt-1 text-[11px] text-gray-500">
                    Shortcut: SSO, SSO-USERS, SSO-ROLES, APPROVAL
                </div>
            </form>
        </div>

        {{-- BODY --}}
        <div class="flex-1 min-h-0 overflow-auto p-3 space-y-4">
            {{-- FAVORITES (tetap) --}}
            @if (!empty($favorites))
                <div class="space-y-2">
                    <div class="text-[10px] font-bold uppercase text-gray-500">Favorites</div>

                    @foreach ($favorites as $m)
                        <div class="flex items-center gap-2">
                            <a href="{{ $m['route'] && \Illuminate\Support\Facades\Route::has($m['route']) ? route($m['route']) : '#' }}"
                                class="flex-1 px-3 py-2 rounded hover:bg-emerald-50">
                                <span class="font-mono text-xs">{{ $m['code'] }}</span>
                                <span class="ml-2 text-sm">{{ $m['name'] }}</span>
                            </a>

                            <button type="button" wire:click="toggleFavorite({{ (int) $m['id'] }})"
                                class="w-9 h-9 rounded hover:bg-gray-100" title="Unfavorite">
                                ⭐
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- MENU TREE --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="text-[10px] font-bold uppercase text-gray-500">Menu (Module → Permission)</div>

                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="expandAllModules"
                            class="px-2 py-1 text-[11px] rounded border border-gray-200 hover:bg-gray-50"
                            title="Expand all modules">Expand</button>
                        <button type="button" wire:click="collapseAllModules"
                            class="px-2 py-1 text-[11px] rounded border border-gray-200 hover:bg-gray-50"
                            title="Collapse all modules">Collapse</button>
                    </div>
                </div>

                <input wire:model.live.debounce.250ms="menuSearch"
                    class="w-full rounded-lg border-gray-300 text-sm px-3 py-2"
                    placeholder="Cari module / permission / nav_code / label / route…" />

                <div class="space-y-2">
                    @forelse($menuTree as $mod)
                        @php
                            $mc = (string) $mod['code'];
                            $openMod = (bool) $mod['open'];
                        @endphp

                        <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                            {{-- MODULE HEADER --}}
                            <div class="flex items-center justify-between gap-2 px-3 py-2 bg-gray-50">
                                <button type="button" wire:click="toggleModuleExpand(@js($mc))"
                                    class="flex-1 text-left flex items-center gap-2 hover:opacity-80">
                                    <span class="text-gray-600">{{ $openMod ? '▼' : '▶' }}</span>
                                    <span class="font-mono text-xs font-bold text-gray-700">{{ $mc }}</span>
                                    <span
                                        class="text-sm font-semibold text-gray-800 truncate">{{ $mod['name'] }}</span>
                                </button>

                                <div class="flex items-center gap-2">
                                    @if (!empty($mod['route']) && \Illuminate\Support\Facades\Route::has($mod['route']))
                                        <a href="{{ route($mod['route']) }}"
                                            class="px-2 py-1 text-[11px] rounded border border-gray-200 hover:bg-white"
                                            title="Open module">Open</a>
                                    @endif

                                    {{-- favorite --}}
                                    <button type="button" wire:click="toggleFavorite({{ (int) $mod['id'] }})"
                                        class="w-8 h-8 rounded hover:bg-white border border-gray-200 flex items-center justify-center"
                                        title="Toggle Favorite">
                                        @php
                                            $isFav = false;
                                            foreach ($modules as $m) {
                                                if ((int) $m['id'] === (int) $mod['id']) {
                                                    $isFav = !empty($m['is_favorite']);
                                                    break;
                                                }
                                            }
                                        @endphp
                                        <span class="{{ $isFav ? 'text-yellow-500' : 'text-gray-400' }}">
                                            {{ $isFav ? '★' : '☆' }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            {{-- MODULE BODY --}}
                            @if ($openMod)
                                <div class="p-2 space-y-2">
                                    @foreach ($mod['perms'] as $perm)
                                        @php
                                            $pc = (string) $perm['code']; // bisa kosong
                                            $openPerm = (bool) $perm['open'];
                                            $label = (string) $perm['label'];
                                        @endphp

                                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                                            {{-- PERMISSION HEADER --}}
                                            <button type="button"
                                                wire:click="togglePermExpand(@js($mc), @js($pc))"
                                                class="w-full px-3 py-2 bg-white hover:bg-gray-50 text-left flex items-center justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-gray-600">{{ $openPerm ? '▼' : '▶' }}</span>
                                                        <span
                                                            class="font-mono text-xs font-bold text-slate-800 truncate">{{ $label }}</span>

                                                        @if ((int) ($perm['requires_approval'] ?? 0) === 1)
                                                            <span
                                                                class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                                                Approval
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if (!empty($perm['description']))
                                                        <div class="text-[11px] text-gray-500 mt-0.5 truncate">
                                                            {{ $perm['description'] }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="flex items-center gap-2 flex-none">
                                                    <span
                                                        class="text-[11px] text-gray-500">{{ (int) $perm['count'] }}</span>
                                                </div>
                                            </button>

                                            {{-- PERMISSION ITEMS --}}
                                            @if ($openPerm)
                                                <div class="py-1">
                                                    @foreach ($perm['items'] as $it)
                                                        @php
                                                            $routeName = trim((string) ($it['route_name'] ?? ''));
                                                            $routeOk = (bool) ($it['route_ok'] ?? false);
                                                            $navCode = (string) ($it['nav_code'] ?? '');
                                                            $path = (string) ($it['path'] ?? '');
                                                        @endphp

                                                        @if ($routeOk && $routeName !== '')
                                                            <a href="{{ route($routeName) }}"
                                                                class="block px-3 py-2 hover:bg-emerald-50">
                                                                <div class="flex items-center gap-2">
                                                                    <span
                                                                        class="w-5 text-center">{{ $it['icon'] ?: '•' }}</span>
                                                                    <span
                                                                        class="text-sm font-semibold text-gray-800">{{ $it['label'] }}</span>
                                                                </div>
                                                                <div class="mt-0.5 text-[11px] text-gray-500">
                                                                    <span class="font-mono">{{ $navCode }}</span>
                                                                    @if ($path !== '')
                                                                        <span class="mx-1">·</span>
                                                                        <span
                                                                            class="truncate">{{ $path }}</span>
                                                                    @endif
                                                                </div>
                                                            </a>
                                                        @else
                                                            <div class="px-3 py-2 text-gray-500">
                                                                <div class="flex items-center gap-2">
                                                                    <span
                                                                        class="w-5 text-center opacity-60">{{ $it['icon'] ?: '▸' }}</span>
                                                                    <span
                                                                        class="text-sm font-semibold">{{ $it['label'] }}</span>
                                                                </div>
                                                                <div class="mt-0.5 text-[11px] text-gray-400 font-mono">
                                                                    {{ $navCode }}</div>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    @empty
                        <div class="text-sm text-gray-400 italic px-2 py-3">
                            Tidak ada menu yang bisa ditampilkan.
                        </div>
                    @endforelse
                </div>
            </div>



        </div>

        <x-ui.sccr-toast :show="$toast['show']" :type="$toast['type']" :message="$toast['message']"
            wire:key="sidebar-toast-{{ $toastSeq }}" />
    </div>
</aside>
