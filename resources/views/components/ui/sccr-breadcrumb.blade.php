@props(['items' => []])

<nav class="text-sm text-gray-600" aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">

                {{-- LINKABLE --}}
                @if (isset($item['route']) && !$loop->last && Route::has($item['route']))
                    {{-- 
                        Gunakan warna dari array jika ada ($item['color']), 
                        jika tidak ada (??) gunakan default 'text-indigo-600'
                    --}}
                    <a href="{{ route($item['route']) }}"
                        class="{{ $item['color'] ?? 'text-indigo-600' }} hover:underline">
                        {{ $item['label'] }}
                    </a>
                @else
                    {{-- 
                        Untuk label non-link (terakhir), jika tidak ada warna khusus,
                        tetap gunakan 'text-gray-800 font-semibold' seperti sebelumnya
                    --}}
                    <span class="{{ $item['color'] ?? 'text-gray-800 font-semibold' }}">
                        {{ $item['label'] }}
                    </span>
                @endif

                @if (!$loop->last)
                    <span class="text-gray-800">/</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
