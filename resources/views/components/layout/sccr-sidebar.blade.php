@props([
    // optional kalau kamu sudah supply menus dari controller/layout
    'menus' => null,
])

@php
    $u = auth()->user();
    $menusTree = $menus;

    if ($menusTree === null && $u) {
        $menusTree = app(\App\Services\NavMenuService::class)->forUser($u);
    }

    $menusTree = $menusTree ?: [];
@endphp

<aside class="w-64 bg-white shadow-lg p-4 h-full overflow-auto">
    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
        Menu
    </div>

    <ul class="space-y-1">
        @foreach ($menusTree as $item)
            <x-layout.sccr-sidebar-item :item="$item" :level="0" />
        @endforeach
    </ul>
</aside>
