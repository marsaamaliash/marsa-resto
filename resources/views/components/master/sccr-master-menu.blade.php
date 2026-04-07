@props(['items'])

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="p-2 rounded hover:bg-gray-200">⋮</button>

    <div x-show="open" x-cloak class="absolute right-8 top-0 bg-white shadow rounded w-48">
        @foreach ($items as $item)
            @can('permission', $item['permission'])
                <button wire:click="{{ $item['action'] }}"
                    class="w-full text-left px-4 py-2 hover:bg-green-600 hover:text-white">
                    {{ $item['label'] }}
                </button>
            @endcan
        @endforeach
    </div>
</div>
