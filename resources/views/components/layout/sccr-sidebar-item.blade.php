@props(['item', 'level' => 0, 'activeRoute' => null])

@php
    $hasChildren = !empty($item['children'] ?? []);
    $indent = $level * 14; // px

    $isActive = false;
    if (!empty($item['route_name'])) {
        $isActive = request()->routeIs($item['route_name']);
    }

    // Open group if any child active
    $childActive = false;
    if ($hasChildren) {
        $stack = $item['children'];
        while (!empty($stack)) {
            $x = array_pop($stack);
            if (!empty($x['route_name']) && request()->routeIs($x['route_name'])) {
                $childActive = true;
                break;
            }
            if (!empty($x['children'])) {
                foreach ($x['children'] as $c) {
                    $stack[] = $c;
                }
            }
        }
    }

    $open = $childActive || $isActive;
@endphp

<li class="select-none">
    @if ($hasChildren)
        <details class="group" @if ($open) open @endif>
            <summary class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-emerald-50 cursor-pointer"
                style="padding-left: {{ 12 + $indent }}px;">
                <span class="w-5 text-center">{{ $item['icon'] ?: '📁' }}</span>
                <span class="flex-1 text-sm font-semibold text-gray-800">{{ $item['label'] }}</span>

                {{-- tampilkan permission root juga --}}
                @if (!empty($item['permission_code']))
                    <span class="text-[10px] font-mono text-gray-500">
                        {{ $item['permission_code'] }}
                    </span>
                @endif

                <span class="text-xs text-gray-500 group-open:hidden">▶</span>
                <span class="text-xs text-gray-500 hidden group-open:inline">▼</span>
            </summary>

            <ul class="mt-1 space-y-1">
                @foreach ($item['children'] as $child)
                    <x-layout.sccr-sidebar-item :item="$child" :level="$level + 1" />
                @endforeach
            </ul>
        </details>
    @else
        @if (!empty($item['route_name']))
            <a href="{{ route($item['route_name']) }}"
                class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-emerald-50
                      {{ $isActive ? 'bg-emerald-100 text-emerald-900 font-bold' : 'text-gray-800' }}"
                style="padding-left: {{ 12 + $indent }}px;">
                <span class="w-5 text-center">{{ $item['icon'] ?: '•' }}</span>
                <span class="flex-1 text-sm">{{ $item['label'] }}</span>

                @if (!empty($item['permission_code']))
                    <span class="text-[10px] font-mono text-gray-500">
                        {{ $item['permission_code'] }}
                    </span>
                @endif
            </a>
        @endif
    @endif
</li>
