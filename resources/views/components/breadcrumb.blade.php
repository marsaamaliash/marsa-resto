@props(['items' => []])

<nav class="text-sm text-gray-600 mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
        @foreach ($items as $item)
            <li>
                @if (!$loop->last)
                    <a href="{{ $item['url'] }}" class="text-indigo-600 hover:underline">
                        {{ $item['label'] }}
                    </a>
                    <span class="mx-2">/</span>
                @else
                    <span class="text-gray-800 font-semibold">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
