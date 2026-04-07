@props(['items' => []])

<nav class="text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">

                {{-- LINKABLE --}}
                @if (isset($item['route']) && !$loop->last && Route::has($item['route']))
                    <a href="{{ route($item['route']) }}" class="text-indigo-600 hover:underline">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-800 font-semibold">
                        {{ $item['label'] }}
                    </span>
                @endif

                @if (!$loop->last)
                    <span class="text-gray-400">/</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
